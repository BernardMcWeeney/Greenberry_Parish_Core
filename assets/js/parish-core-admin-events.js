/**
 * Parish Core Admin - Events Calendar & List View
 */
(function (window) {
	'use strict';

	const {
		el,
		useState,
		useEffect,
		useMemo,
		useCallback,
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
	// UTILITY FUNCTIONS
	// =========================================================================
	const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

	function formatDate(dateStr) {
		if (!dateStr) return '';
		var d = new Date(dateStr + 'T00:00:00');
		return d.toLocaleDateString('en-IE', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
	}

	function formatTime(timeStr) {
		if (!timeStr) return '';
		var parts = timeStr.split(':');
		var h = parseInt(parts[0], 10);
		var m = parts[1] || '00';
		var ampm = h >= 12 ? 'PM' : 'AM';
		h = h % 12 || 12;
		return h + ':' + m + ' ' + ampm;
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

		// Pad end to complete grid (6 rows)
		const remaining = 42 - dates.length;
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
	// CALENDAR VIEW COMPONENT
	// =========================================================================
	function CalendarView({ events, year, month, onDateClick, onEventClick }) {
		const dates = useMemo(() => getMonthDates(year, month), [year, month]);

		// Group events by date
		const eventsByDate = useMemo(() => {
			const grouped = {};
			events.forEach(evt => {
				if (evt.date) {
					if (!grouped[evt.date]) grouped[evt.date] = [];
					grouped[evt.date].push(evt);
				}
			});
			return grouped;
		}, [events]);

		const weeks = [];
		for (let i = 0; i < dates.length; i += 7) {
			weeks.push(dates.slice(i, i + 7));
		}

		return el('div', { className: 'events-calendar' },
			el('div', { className: 'calendar-header', style: { display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)', gap: '1px', marginBottom: '8px' } },
				WEEKDAYS.map(day =>
					el('div', { key: day, style: { textAlign: 'center', fontWeight: '600', padding: '8px', background: '#f0f0f1' } }, day)
				)
			),
			el('div', { className: 'calendar-body' },
				weeks.map((week, weekIdx) =>
					el('div', { key: weekIdx, className: 'calendar-week', style: { display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)', gap: '1px' } },
						week.map(day => {
							const dayEvents = eventsByDate[day.date] || [];
							return el('div', {
								key: day.date,
								className: 'calendar-day',
								onClick: () => onDateClick(day.date),
								style: {
									minHeight: '100px',
									padding: '4px',
									background: day.isCurrentMonth ? '#fff' : '#f9f9f9',
									border: day.isToday ? '2px solid #2271b1' : '1px solid #e0e0e0',
									cursor: 'pointer',
								},
							},
								el('div', { style: { fontWeight: day.isToday ? '700' : '500', marginBottom: '4px', color: day.isCurrentMonth ? '#1e1e1e' : '#999' } }, day.dayNum),
								el('div', { className: 'day-events' },
									dayEvents.slice(0, 3).map((evt, idx) =>
										el('div', {
											key: idx,
											onClick: (e) => { e.stopPropagation(); onEventClick(evt); },
											style: {
												fontSize: '11px',
												padding: '2px 4px',
												marginBottom: '2px',
												background: evt.featured ? '#dba617' : '#e8f4fc',
												color: evt.featured ? '#fff' : '#1a5a8e',
												borderRadius: '2px',
												overflow: 'hidden',
												textOverflow: 'ellipsis',
												whiteSpace: 'nowrap',
												cursor: 'pointer',
											}
										}, evt.title || '(No title)')
									),
									dayEvents.length > 3 && el('div', { style: { fontSize: '10px', color: '#666' } }, '+' + (dayEvents.length - 3) + ' more')
								)
							);
						})
					)
				)
			)
		);
	}

	// =========================================================================
	// LIST VIEW COMPONENT
	// =========================================================================
	function ListView({ events, onEdit, onDelete, deleting }) {
		if (events.length === 0) {
			return el('div', { className: 'no-events', style: { padding: '40px', textAlign: 'center', background: '#f9f9f9', borderRadius: '4px' } },
				el('p', { style: { margin: 0, color: '#666' } }, 'No events found.')
			);
		}

		return el('table', { className: 'widefat striped' },
			el('thead', null,
				el('tr', null,
					el('th', null, 'Event'),
					el('th', { style: { width: '130px' } }, 'Date'),
					el('th', { style: { width: '80px' } }, 'Time'),
					el('th', { style: { width: '130px' } }, 'Location'),
					el('th', { style: { width: '100px' } }, 'Church'),
					el('th', { style: { width: '120px' } }, 'Category'),
					el('th', { style: { width: '110px' } }, 'Actions')
				)
			),
			el('tbody', null,
				events.map(evt =>
					el('tr', { key: evt.id },
						el('td', null,
							el('span', {
								onClick: () => onEdit(evt),
								style: { fontWeight: 500, textDecoration: 'none', cursor: 'pointer', color: '#0073aa' },
							}, evt.title || '(No title)'),
							evt.featured && el('span', {
								style: {
									marginLeft: '8px',
									fontSize: '10px',
									background: '#dba617',
									color: '#fff',
									padding: '2px 6px',
									borderRadius: '3px',
								},
							}, 'Featured'),
							evt.cemetery_id > 0 && el('span', {
								style: {
									marginLeft: '4px',
									fontSize: '10px',
									background: '#666',
									color: '#fff',
									padding: '2px 6px',
									borderRadius: '3px',
								},
							}, 'Cemetery')
						),
						el('td', null, formatDate(evt.date)),
						el('td', null, formatTime(evt.time)),
						el('td', null, evt.location || '—'),
						el('td', null, evt.church_name || '—'),
						el('td', { style: { fontSize: '12px' } }, evt.sacrament || '—'),
						el('td', null,
							el(Flex, { gap: 1 },
								el(Button, {
									isSmall: true,
									variant: 'secondary',
									onClick: () => onEdit(evt),
								}, 'Edit'),
								el(Button, {
									isSmall: true,
									isDestructive: true,
									isBusy: deleting === evt.id,
									onClick: () => onDelete(evt.id),
								}, 'Delete')
							)
						)
					)
				)
			)
		);
	}

	// =========================================================================
	// EVENT MODAL COMPONENT
	// =========================================================================
	function EventModal({ event, churches, cemeteries, taxonomies, isNew, onSave, onClose, onDelete, onRefreshTaxonomies }) {
		const [form, setForm] = useState(event || {});
		const [saving, setSaving] = useState(false);
		const [deleting, setDeleting] = useState(false);
		const [showAddCategory, setShowAddCategory] = useState(false);
		const [newCategoryName, setNewCategoryName] = useState('');
		const [addingCategory, setAddingCategory] = useState(false);

		const handleDelete = () => {
			if (!form.id || isNew) return;
			if (!confirm('Are you sure you want to delete this event?')) return;
			setDeleting(true);
			onDelete(form.id);
		};

		const getEditUrl = (id) => {
			const adminUrl = (window.parishCore && window.parishCore.adminUrl) || '/wp-admin/';
			return adminUrl + 'post.php?post=' + id + '&action=edit';
		};

		const handleOpenFullEditor = () => {
			if (form.id) {
				window.location.href = getEditUrl(form.id);
			}
		};

		const upd = (k, v) => setForm(prev => ({ ...prev, [k]: v }));

		const churchOptions = [{ label: 'Select Church', value: 0 }].concat(
			(churches || []).map(c => ({ label: c.title, value: c.id }))
		);

		const cemeteryOptions = [{ label: 'No Cemetery', value: 0 }].concat(
			(cemeteries || []).map(c => ({ label: c.title, value: c.id }))
		);

		const sacramentOptions = [{ label: 'Select Category', value: 0 }].concat(
			(taxonomies.sacraments || []).map(s => ({ label: s.name, value: s.term_id }))
		);

		const handleSave = () => {
			setSaving(true);
			onSave(form);
		};

		const handleAddCategory = () => {
			if (!newCategoryName.trim()) return;
			setAddingCategory(true);
			apiFetch({
				path: '/wp/v2/parish_sacrament',
				method: 'POST',
				data: { name: newCategoryName.trim() },
			})
				.then(term => {
					upd('sacrament_id', term.id);
					setNewCategoryName('');
					setShowAddCategory(false);
					setAddingCategory(false);
					if (onRefreshTaxonomies) onRefreshTaxonomies();
				})
				.catch(() => {
					setAddingCategory(false);
				});
		};

		return el(Modal, {
			title: isNew ? 'Add Event' : 'Edit Event',
			onRequestClose: onClose,
			className: 'parish-modal event-modal',
			style: { maxWidth: '600px' },
		},
			el('div', { className: 'modal-form' },
				el(TextControl, {
					label: 'Event Title',
					value: form.title || '',
					onChange: v => upd('title', v),
					placeholder: 'Enter event title...',
				}),
				el(Flex, { gap: 3 },
					el(FlexBlock, null,
						el(TextControl, {
							label: 'Date',
							type: 'date',
							value: form.date || '',
							onChange: v => upd('date', v),
						})
					),
					el(FlexBlock, null,
						el(TextControl, {
							label: 'Time',
							type: 'time',
							value: form.time || '',
							onChange: v => upd('time', v),
						})
					)
				),
				el(TextControl, {
					label: 'Location',
					value: form.location || '',
					onChange: v => upd('location', v),
					placeholder: 'Event venue or address...',
				}),
				el(TextControl, {
					label: 'Event URL',
					type: 'url',
					value: form.registration_url || '',
					onChange: v => upd('registration_url', v),
					placeholder: 'https://...',
					help: 'Optional link for registration or event details',
				}),
				el(SelectControl, {
					label: 'Church',
					value: form.church_id || 0,
					options: churchOptions,
					onChange: v => upd('church_id', parseInt(v, 10)),
					__nextHasNoMarginBottom: true,
				}),
				el('div', { style: { marginBottom: '16px' } },
					el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '500' } }, 'Category'),
					el(Flex, { gap: 2, align: 'flex-start' },
						el(FlexBlock, null,
							!showAddCategory && el(SelectControl, {
								value: form.sacrament_id || 0,
								options: sacramentOptions,
								onChange: v => upd('sacrament_id', parseInt(v, 10)),
								__nextHasNoMarginBottom: true,
							}),
							showAddCategory && el(Flex, { gap: 2 },
								el(FlexBlock, null,
									el(TextControl, {
										value: newCategoryName,
										onChange: setNewCategoryName,
										placeholder: 'New category name...',
									})
								),
								el(FlexItem, null,
									el(Button, {
										variant: 'primary',
										isSmall: true,
										isBusy: addingCategory,
										onClick: handleAddCategory,
										disabled: !newCategoryName.trim(),
									}, 'Add')
								),
								el(FlexItem, null,
									el(Button, {
										variant: 'secondary',
										isSmall: true,
										onClick: () => { setShowAddCategory(false); setNewCategoryName(''); },
									}, 'Cancel')
								)
							)
						),
						!showAddCategory && el(FlexItem, null,
							el(Button, {
								variant: 'secondary',
								isSmall: true,
								onClick: () => setShowAddCategory(true),
							}, '+ New')
						)
					)
				),
				el(TextareaControl, {
					label: 'Description',
					value: form.description || '',
					onChange: v => upd('description', v),
					rows: 3,
					placeholder: 'Brief description of the event...',
				}),
				el(Flex, { gap: 3 },
					el(FlexBlock, null,
						el(SelectControl, {
							label: 'Cemetery',
							value: form.cemetery_id || 0,
							options: cemeteryOptions,
							onChange: v => upd('cemetery_id', parseInt(v, 10)),
							__nextHasNoMarginBottom: true,
						})
					),
					el(FlexBlock, null,
						el(ToggleControl, {
							label: 'Featured Event',
							checked: form.featured || false,
							onChange: v => upd('featured', v),
							__nextHasNoMarginBottom: true,
						})
					)
				)
			),
			el('div', { className: 'modal-actions', style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingTop: '16px', borderTop: '1px solid #ddd', marginTop: '16px' } },
				!isNew && form.id
					? el(Flex, { gap: 2 },
						el(Button, {
							isDestructive: true,
							isBusy: deleting,
							onClick: handleDelete,
						}, deleting ? 'Deleting...' : 'Delete Event'),
						el(Button, {
							variant: 'secondary',
							onClick: handleOpenFullEditor,
						}, 'Edit in Full Editor')
					)
					: el('div'),
				el('div', { style: { display: 'flex', gap: '8px' } },
					el(Button, { variant: 'secondary', onClick: onClose }, 'Cancel'),
					el(Button, { variant: 'primary', isBusy: saving, onClick: handleSave },
						saving ? 'Saving...' : 'Save Event'
					)
				)
			)
		);
	}

	// =========================================================================
	// MAIN EVENTS CALENDAR COMPONENT
	// =========================================================================
	function EventsCalendar() {
		const [events, setEvents] = useState([]);
		const [churches, setChurches] = useState([]);
		const [cemeteries, setCemeteries] = useState([]);
		const [taxonomies, setTaxonomies] = useState({ sacraments: [], types: [], locations: [] });
		const [loading, setLoading] = useState(true);
		const [notice, setNotice] = useState(null);
		const [view, setView] = useState('calendar'); // 'calendar' or 'list'
		const [filter, setFilter] = useState('upcoming');
		const [churchFilter, setChurchFilter] = useState(0);
		const [sacramentFilter, setSacramentFilter] = useState(0);
		const [cemeteryFilter, setCemeteryFilter] = useState(false);
		const [search, setSearch] = useState('');
		const [deleting, setDeleting] = useState(null);
		const [monthYear, setMonthYear] = useState({ year: new Date().getFullYear(), month: new Date().getMonth() });
		const [editing, setEditing] = useState(null);
		const [isNew, setIsNew] = useState(false);

		const loadEvents = useCallback(function () {
			setLoading(true);
			let path = '/parish/v1/events/list?filter=' + filter + '&church=' + churchFilter + '&sacrament=' + sacramentFilter;
			if (cemeteryFilter) {
				path += '&cemetery=true';
			}
			apiFetch({ path })
				.then(function (res) {
					setEvents(res || []);
					setLoading(false);
				})
				.catch(function (err) {
					console.error('Failed to load events:', err);
					setLoading(false);
					setNotice({ type: 'error', message: 'Failed to load events.' });
				});
		}, [filter, churchFilter, sacramentFilter, cemeteryFilter]);

		const loadTaxonomies = useCallback(function () {
			apiFetch({ path: '/parish/v1/events/taxonomies' })
				.then(function (res) {
					var sacraments = Array.isArray(res && res.sacraments) ? res.sacraments : [];
					var types = Array.isArray(res && res.types) ? res.types : [];
					var locations = Array.isArray(res && res.locations) ? res.locations : [];
					setTaxonomies({ sacraments: sacraments, types: types, locations: locations });
				})
				.catch(function () {
					setTaxonomies({ sacraments: [], types: [], locations: [] });
				});
		}, []);

		useEffect(function () {
			// Load churches for filter
			apiFetch({ path: '/parish/v1/churches/list' })
				.then(function (res) {
					setChurches(res || []);
				})
				.catch(function () {});

			// Load cemeteries for event selection
			apiFetch({ path: '/wp/v2/parish_cemetery?per_page=100' })
				.then(function (res) {
					// Convert WP REST API format to simple format
					setCemeteries(Array.isArray(res) ? res.map(function(c) {
						return { id: c.id, title: c.title.rendered || c.title };
					}) : []);
				})
				.catch(function () {
					setCemeteries([]);
				});

			// Load taxonomies for filters
			loadTaxonomies();

			loadEvents();
		}, []);

		useEffect(function () {
			loadEvents();
		}, [loadEvents]);

		const deleteEvent = function (id, fromModal) {
			if (!fromModal && !confirm('Are you sure you want to delete this event?')) {
				return;
			}
			setDeleting(id);
			apiFetch({
				path: '/wp/v2/events/' + id,
				method: 'DELETE',
			})
				.then(function () {
					setNotice({ type: 'success', message: 'Event deleted.' });
					setDeleting(null);
					setEditing(null);
					loadEvents();
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: err.message || 'Failed to delete.' });
					setDeleting(null);
				});
		};

		const deleteAllPast = function () {
			if (!confirm('Delete ALL past events? This cannot be undone.')) {
				return;
			}
			setLoading(true);
			apiFetch({
				path: '/parish/v1/events/delete-past',
				method: 'POST',
			})
				.then(function (res) {
					setNotice({ type: 'success', message: res.message || 'Past events deleted.' });
					loadEvents();
				})
				.catch(function (err) {
					setNotice({ type: 'error', message: err.message || 'Failed to delete past events.' });
					setLoading(false);
				});
		};

		// Options for filters
		const churchOptions = [{ label: 'All Churches', value: 0 }].concat(
			(churches || []).map(function (c) {
				return { label: c.title, value: c.id };
			})
		);

		const sacramentOptions = [{ label: 'All Categories', value: 0 }].concat(
			(taxonomies.sacraments || []).map(function (s) {
				return { label: s.name, value: s.term_id };
			})
		);

		const filterOptions = [
			{ label: 'Upcoming Events', value: 'upcoming' },
			{ label: 'Past Events', value: 'past' },
			{ label: 'All Events', value: 'all' },
		];

		// Filter by search term
		var filteredEvents = events;
		if (search) {
			var term = search.toLowerCase();
			filteredEvents = events.filter(function (e) {
				return (e.title || '').toLowerCase().indexOf(term) !== -1 ||
					(e.location || '').toLowerCase().indexOf(term) !== -1 ||
					(e.sacrament || '').toLowerCase().indexOf(term) !== -1;
			});
		}

		// Get edit/add URLs
		const getEditUrl = function (id) {
			return window.parishCoreAdmin && window.parishCoreAdmin.adminUrl
				? window.parishCoreAdmin.adminUrl + 'post.php?post=' + id + '&action=edit'
				: '/wp-admin/post.php?post=' + id + '&action=edit';
		};

		const getAddUrl = function () {
			return window.parishCoreAdmin && window.parishCoreAdmin.adminUrl
				? window.parishCoreAdmin.adminUrl + 'post-new.php?post_type=parish_event'
				: '/wp-admin/post-new.php?post_type=parish_event';
		};

		// Calendar navigation
		const monthName = useMemo(() => {
			const d = new Date(monthYear.year, monthYear.month, 1);
			return d.toLocaleDateString('en-IE', { month: 'long', year: 'numeric' });
		}, [monthYear]);

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
			setMonthYear({ year: new Date().getFullYear(), month: new Date().getMonth() });
		};

		const handleEventClick = (evt) => {
			setIsNew(false);
			setEditing({
				id: evt.id,
				title: evt.title,
				date: evt.date,
				time: evt.time,
				location: evt.location,
				registration_url: evt.registration_url || '',
				church_id: evt.church_id || 0,
				cemetery_id: evt.cemetery_id || 0,
				sacrament_id: evt.sacrament_id || 0,
				description: evt.description || '',
				featured: evt.featured || false,
			});
		};

		const handleDateClick = (date) => {
			setIsNew(true);
			setEditing({
				title: '',
				date: date,
				time: '10:00',
				location: '',
				registration_url: '',
				church_id: churchFilter || 0,
				cemetery_id: 0,
				sacrament_id: sacramentFilter || 0,
				description: '',
				featured: false,
			});
		};

		const handleAdd = () => {
			const today = new Date().toISOString().split('T')[0];
			handleDateClick(today);
		};

		const handleSaveEvent = (data) => {
			const path = isNew ? '/wp/v2/events' : '/wp/v2/events/' + data.id;
			const method = 'POST';

			// Format data for WordPress REST API
			const postData = {
				title: data.title || '',
				content: data.description || '',
				status: 'publish',
				meta: {
					parish_event_date: data.date || '',
					parish_event_time: data.time || '',
					parish_event_location: data.location || '',
					parish_event_registration_url: data.registration_url || '',
					parish_event_church_id: data.church_id || 0,
					parish_event_cemetery_id: data.cemetery_id || 0,
					parish_event_featured: !!data.featured,
				},
			};

			// Add taxonomy if set
			if (data.sacrament_id) {
				postData.parish_sacrament = [data.sacrament_id];
			}

			apiFetch({ path, method, data: postData })
				.then(() => {
					setEditing(null);
					setNotice({
						type: 'success',
						message: isNew ? 'Event created!' : 'Event updated!',
					});
					loadEvents();
				})
				.catch(err => {
					setNotice({ type: 'error', message: err.message });
				});
		};

		return el('div', { className: 'parish-events-page' },
			notice && el(Notice, {
				status: notice.type,
				isDismissible: true,
				onRemove: () => setNotice(null),
			}, notice.message),

			// Toolbar
			el('div', { className: 'events-toolbar', style: {
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
				el('div', { style: { display: 'flex', gap: '4px' } },
					el(Button, {
						isSmall: true,
						variant: view === 'list' ? 'primary' : 'secondary',
						onClick: () => setView('list'),
					}, 'List'),
					el(Button, {
						isSmall: true,
						variant: view === 'calendar' ? 'primary' : 'secondary',
						onClick: () => setView('calendar'),
					}, 'Calendar')
				),

				// Calendar navigation (only for calendar view)
				view === 'calendar' && el('div', { style: { display: 'flex', alignItems: 'center', gap: '8px' } },
					el(Button, { isSmall: true, onClick: prevMonth }, '←'),
					el('span', { style: { fontWeight: '500', minWidth: '150px', textAlign: 'center' } }, monthName),
					el(Button, { isSmall: true, onClick: nextMonth }, '→'),
					el(Button, { isSmall: true, variant: 'tertiary', onClick: goToday }, 'Today')
				),

				// Filters
				el(SelectControl, {
					value: filter,
					options: filterOptions,
					onChange: setFilter,
					__nextHasNoMarginBottom: true,
				}),
				el(SelectControl, {
					value: churchFilter,
					options: churchOptions,
					onChange: v => setChurchFilter(parseInt(v, 10)),
					__nextHasNoMarginBottom: true,
				}),
				el(SelectControl, {
					value: sacramentFilter,
					options: sacramentOptions,
					onChange: v => setSacramentFilter(parseInt(v, 10)),
					__nextHasNoMarginBottom: true,
				}),
				el(ToggleControl, {
					label: 'Cemetery',
					checked: cemeteryFilter,
					onChange: setCemeteryFilter,
					__nextHasNoMarginBottom: true,
				}),
				el(TextControl, {
					value: search,
					onChange: setSearch,
					placeholder: 'Search...',
					__nextHasNoMarginBottom: true,
					style: { maxWidth: '180px' },
				}),

				// Spacer
				el('div', { style: { flex: 1 } }),

				// Actions
				filter === 'past' && events.length > 0 && el(Button, {
					isDestructive: true,
					isSmall: true,
					onClick: deleteAllPast,
				}, 'Delete All Past'),
				el(Button, {
					variant: 'primary',
					onClick: handleAdd,
				}, '+ Add Event')
			),

			// Content
			loading
				? el(LoadingSpinner, { text: 'Loading events...' })
				: view === 'calendar'
				? el(CalendarView, {
					events: filteredEvents,
					year: monthYear.year,
					month: monthYear.month,
					onDateClick: handleDateClick,
					onEventClick: handleEventClick,
				})
				: el(ListView, {
					events: filteredEvents,
					onEdit: handleEventClick,
					onDelete: deleteEvent,
					deleting,
				}),

			// Footer
			!loading && el('p', { style: { marginTop: '16px', color: '#666', fontSize: '13px' } },
				'Showing ' + filteredEvents.length + ' event' + (filteredEvents.length !== 1 ? 's' : '') + '.'
			),

			// Event Modal
			editing && el(EventModal, {
				event: editing,
				churches: churches,
				cemeteries: cemeteries,
				taxonomies: taxonomies,
				isNew: isNew,
				onSave: handleSaveEvent,
				onClose: () => setEditing(null),
				onDelete: (id) => deleteEvent(id, true),
				onRefreshTaxonomies: loadTaxonomies,
			})
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		EventsCalendar,
	});
})(window);
