/**
 * Parish Core Admin - Mass Times
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
		ToggleControl,
		Flex,
		apiFetch,
		days,
		LoadingSpinner,
		generateId,
	} = window.ParishCoreAdmin;

	function MassModal(props) {
		const mass = props.mass;
		const churches = props.churches || [];
		const onSave = props.onSave;
		const onDelete = props.onDelete;
		const onClose = props.onClose;

		const [form, setForm] = useState(mass);
		const upd = function (k, v) {
			setForm(function (p) {
				return Object.assign({}, p, { [k]: v });
			});
		};

		return el(
			Modal,
			{ title: 'Mass Time', onRequestClose: onClose, className: 'parish-modal' },
			el(
				'div',
				{ className: 'modal-form' },
				el(SelectControl, {
					label: 'Day',
					value: form.day,
					options: days.map(function (d) {
						return { label: d, value: d };
					}),
					onChange: function (v) {
						upd('day', v);
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
				churches.length > 0 &&
					el(SelectControl, {
						label: 'Church',
						value: form.church_id,
						options: [{ label: '-- Select --', value: 0 }].concat(
							churches.map(function (c) {
								return { label: c.title, value: c.id };
							})
						),
						onChange: function (v) {
							upd('church_id', parseInt(v, 10));
						},
					}),
				el(ToggleControl, {
					label: 'Livestreamed',
					checked: form.is_livestreamed,
					onChange: function (v) {
						upd('is_livestreamed', v);
					},
				}),
				form.is_livestreamed &&
					el(TextControl, {
						label: 'Livestream URL',
						type: 'url',
						value: form.livestream_url,
						onChange: function (v) {
							upd('livestream_url', v);
						},
					}),
				el(TextareaControl, {
					label: 'Notes',
					value: form.notes,
					rows: 2,
					onChange: function (v) {
						upd('notes', v);
					},
				}),
				el(ToggleControl, {
					label: 'Active',
					checked: form.active,
					onChange: function (v) {
						upd('active', v);
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

	function MassTimes() {
		const [massTimes, setMassTimes] = useState([]);
		const [churches, setChurches] = useState([]);
		const [loading, setLoading] = useState(true);
		const [saving, setSaving] = useState(false);
		const [notice, setNotice] = useState(null);
		const [editing, setEditing] = useState(null);

		useEffect(function () {
			apiFetch({ path: '/parish/v1/mass-times' })
				.then(function (res) {
					setMassTimes(res.mass_times || []);
					setChurches(res.churches || []);
					setLoading(false);
				})
				.catch(function () {
					setLoading(false);
				});
		}, []);

		const save = function () {
			setSaving(true);
			apiFetch({
				path: '/parish/v1/mass-times',
				method: 'POST',
				data: { mass_times: massTimes },
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

		const add = function () {
			setEditing({
				id: generateId(),
				day: 'Sunday',
				time: '10:00',
				church_id: 0,
				is_recurring: true,
				recurrence_type: 'weekly',
				is_livestreamed: false,
				livestream_url: '',
				notes: '',
				active: true,
			});
		};

		const saveMass = function (mass) {
			const idx = massTimes.findIndex(function (m) {
				return m.id === mass.id;
			});
			if (idx >= 0) {
				const u = massTimes.slice();
				u[idx] = mass;
				setMassTimes(u);
			} else {
				setMassTimes(massTimes.concat([mass]));
			}
			setEditing(null);
		};

		const deleteMass = function (id) {
			setMassTimes(
				massTimes.filter(function (m) {
					return m.id !== id;
				})
			);
		};

		if (loading) return el(LoadingSpinner, { text: 'Loading...' });

		var byDay = {};
		days.forEach(function (d) {
			byDay[d] = [];
		});
		massTimes.forEach(function (mt) {
			if (byDay[mt.day]) byDay[mt.day].push(mt);
		});

		return el(
			'div',
			{ className: 'parish-mass-times-page' },
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
					'p',
					{ className: 'description' },
					'Click on a mass time to edit. Click "Save All" to persist changes.'
				),
				el(
					Button,
					{ isPrimary: true, onClick: add },
					'+ Add Mass Time'
				)
			),
			el(
				'div',
				{ className: 'mass-times-grid' },
				days.map(function (day) {
					return el(
						'div',
						{ key: day, className: 'mass-day-column' },
						el('h3', null, day),
						byDay[day].length === 0
							? el(
									'p',
									{ className: 'no-masses' },
									'No masses'
							  )
							: byDay[day].map(function (mass) {
									var church = churches.find(function (c) {
										return c.id === mass.church_id;
									});
									return el(
										'div',
										{
											key: mass.id,
											className:
												'mass-item' +
												(mass.active ? '' : ' inactive'),
											onClick: function () {
												setEditing(mass);
											},
										},
										el(
											'div',
											{ className: 'mass-time' },
											mass.time
										),
										church &&
											el(
												'div',
												{ className: 'mass-church' },
												church.title
											),
										mass.is_livestreamed &&
											el(
												'span',
												{ className: 'livestream-badge' },
												'📺'
											)
									);
							  })
					);
				})
			),
			editing &&
				el(MassModal, {
					mass: editing,
					churches: churches,
					onSave: saveMass,
					onDelete: function () {
						deleteMass(editing.id);
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
		MassTimes,
	});
})(window);
