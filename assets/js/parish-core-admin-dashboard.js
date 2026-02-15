(function (window) {
	'use strict';
	const { el, useState, useEffect, Fragment, Card, CardHeader, CardBody, CardFooter, Flex, FlexItem, FlexBlock, Button, Notice, LoadingSpinner, apiFetch, config } = window.ParishCoreAdmin;

	function LiturgicalCard(props) {
		const liturgical = props.liturgical || {};
		const colorMap = { green: '#2e7d32', white: '#9e9e9e', red: '#c62828', violet: '#6a1b9a', purple: '#6a1b9a', rose: '#e91e63', black: '#212121', gold: '#ffc107' };
		const rawColor = (liturgical.color || '').toLowerCase();
		const bgColor = colorMap[rawColor] || '#2271b1';
		const textColor = ['white', 'gold'].includes(rawColor) ? '#333' : '#fff';
		return el(Card, { className: 'dashboard-card liturgical-card', style: { background: bgColor, color: textColor } },
			el(CardBody, null,
				el('div', { className: 'liturgical-season' }, liturgical.season || 'Ordinary Time'),
				liturgical.week && el('div', { className: 'liturgical-week' }, 'Week ' + liturgical.week),
				liturgical.feast_day && el('div', { className: 'liturgical-feast' }, liturgical.feast_day),
				el('div', { className: 'liturgical-date' }, liturgical.formatted_date)
			)
		);
	}

	function SecretaryPrioritiesCard(props) {
		const { stats, features } = props;

		const priorities = [
			{ key: 'parish_newsletter', label: 'Newsletters', feature: 'newsletters', icon: 'media-document', newUrl: 'post-new.php?post_type=parish_newsletter', editUrl: 'edit.php?post_type=parish_newsletter' },
			{ key: 'parish_death_notice', label: 'Death Notices', feature: 'death_notices', icon: 'heart', newUrl: 'post-new.php?post_type=parish_death_notice', editUrl: 'edit.php?post_type=parish_death_notice' },
			{ key: 'parish_reflection', label: 'Reflections', feature: 'reflections', icon: 'format-quote', newUrl: 'post-new.php?post_type=parish_reflection', editUrl: 'edit.php?post_type=parish_reflection' },
			{ key: 'post', label: 'Notices/Posts', feature: 'news', icon: 'megaphone', newUrl: 'post-new.php', editUrl: 'edit.php' },
			{ key: 'parish_events', label: 'Events', feature: 'events', icon: 'calendar-alt', newUrl: 'post-new.php?post_type=parish_events', editUrl: 'edit.php?post_type=parish_events' },
		];

		const activePriorities = priorities.filter(function (p) {
			return features[p.feature] !== false;
		});

		return el(Card, { className: 'dashboard-card secretary-priority-card' },
			el(CardHeader, null, el('strong', null, 'Content Overview')),
			el(CardBody, null,
				el('div', { className: 'priority-grid' },
					activePriorities.map(function (item) {
						const stat = stats[item.key] || {};
						return el('a', {
							key: item.key,
							href: config.adminUrl + item.editUrl,
							className: 'priority-tile'
						},
							el('span', { className: 'dashicons dashicons-' + item.icon }),
							el('span', { className: 'priority-label' }, item.label),
							el('span', { className: 'priority-count' }, stat.published || 0),
							stat.draft > 0 && el('span', { className: 'priority-draft' }, stat.draft + ' draft' + (stat.draft > 1 ? 's' : ''))
						);
					})
				)
			)
		);
	}

	function QuickActionsCard(props) {
		const { features } = props;

		const actions = [
			{ label: 'Add Newsletter', icon: 'media-document', url: config.adminUrl + 'post-new.php?post_type=parish_newsletter', feature: 'newsletters' },
			{ label: 'Add Death Notice', icon: 'heart', url: config.adminUrl + 'post-new.php?post_type=parish_death_notice', feature: 'death_notices' },
			{ label: 'Add Event', icon: 'calendar-alt', url: config.adminUrl + 'post-new.php?post_type=parish_events', feature: 'events' },
			{ label: 'Add Reflection', icon: 'format-quote', url: config.adminUrl + 'post-new.php?post_type=parish_reflection', feature: 'reflections' },
			{ label: 'Add Post', icon: 'edit', url: config.adminUrl + 'post-new.php', feature: 'news' },
			{ label: 'Upload Media', icon: 'upload', url: config.adminUrl + 'media-new.php', feature: null },
		];

		const activeActions = actions.filter(function (a) {
			return a.feature === null || features[a.feature] !== false;
		}).slice(0, 6);

		return el(Card, { className: 'dashboard-card quick-actions-card' },
			el(CardHeader, null, el('strong', null, 'Quick Actions')),
			el(CardBody, null,
				el('div', { className: 'quick-actions-grid' },
					activeActions.map(function (action, i) {
						return el('a', { key: i, href: action.url, className: 'quick-action-item' },
							el('span', { className: 'dashicons dashicons-' + (action.icon || 'plus-alt') }),
							el('span', null, action.label)
						);
					})
				)
			)
		);
	}

	function UpcomingEventsCard(props) {
		const { events, features } = props;

		if (features.events === false || !events || events.length === 0) {
			return null;
		}

		return el(Card, { className: 'dashboard-card upcoming-events-card' },
			el(CardHeader, null, el('strong', null, 'Upcoming Events')),
			el(CardBody, null,
				el('ul', { className: 'events-list' },
					events.slice(0, 5).map(function (event, i) {
						return el('li', { key: i },
							el('span', { className: 'event-date' }, event.date_display || event.date),
							el('span', { className: 'event-title' }, event.title),
							event.time && el('span', { className: 'event-time' }, event.time)
						);
					})
				)
			),
			el(CardFooter, null,
				el('a', { href: config.adminUrl + 'admin.php?page=parish-events' }, 'View All Events')
			)
		);
	}

	function MassTimesCard(props) {
		const { massTimes, features } = props;

		if (features.mass_times === false || !massTimes || massTimes.length === 0) {
			return null;
		}

		return el(Card, { className: 'dashboard-card mass-times-card' },
			el(CardHeader, null, el('strong', null, 'Upcoming Masses')),
			el(CardBody, null,
				el('ul', { className: 'mass-times-list' },
					massTimes.slice(0, 5).map(function (mass, i) {
						return el('li', { key: i },
							el('strong', null, mass.day_name || mass.date_display || ''),
							el('span', null, ' — '),
							el('span', null, mass.time_display || mass.time),
							mass.church_name && el('em', null, ' at ' + mass.church_name),
							mass.is_livestreamed && el('span', { className: 'livestream-badge' }, 'Live')
						);
					})
				)
			),
			el(CardFooter, null,
				el('a', { href: config.adminUrl + 'admin.php?page=parish-mass-times' }, 'Manage Mass Times')
			)
		);
	}

	function ReadingsCard(props) {
		const { readings, features } = props;

		if (features.liturgical === false || !readings) {
			return null;
		}

		return el(Card, { className: 'dashboard-card readings-card' },
			el(CardHeader, null, el('strong', null, "Today's Readings")),
			el(CardBody, null,
				readings.first_reading && el('p', null, el('strong', null, 'First Reading: '), readings.first_reading),
				readings.psalm && el('p', null, el('strong', null, 'Psalm: '), readings.psalm),
				readings.second_reading && el('p', null, el('strong', null, 'Second Reading: '), readings.second_reading),
				readings.gospel && el('p', null, el('strong', null, 'Gospel: '), readings.gospel)
			)
		);
	}

	function RecentNewslettersCard(props) {
		const { newsletters, features } = props;

		if (features.newsletters === false || !newsletters || newsletters.length === 0) {
			return null;
		}

		return el(Card, { className: 'dashboard-card recent-newsletters-card' },
			el(CardHeader, null, el('strong', null, 'Recent Newsletters')),
			el(CardBody, null,
				el('ul', { className: 'newsletters-list' },
					newsletters.slice(0, 3).map(function (item, i) {
						return el('li', { key: i },
							el('a', { href: item.edit_url || '#' }, item.title),
							el('span', { className: 'newsletter-date' }, item.date)
						);
					})
				)
			),
			el(CardFooter, null,
				el('a', { href: config.adminUrl + 'edit.php?post_type=parish_newsletter' }, 'View All Newsletters')
			)
		);
	}

	function ReflectionCard(props) {
		const { reflection, features } = props;

		if (features.reflections === false || !reflection) {
			return null;
		}

		return el(Card, { className: 'dashboard-card reflection-card' },
			el(CardHeader, null, el('strong', null, 'Daily Reflection')),
			el(CardBody, null,
				el('blockquote', null, '"' + (reflection.content || '').substring(0, 200) + (reflection.content && reflection.content.length > 200 ? '...' : '') + '"'),
				el('cite', null, '— ' + reflection.title)
			)
		);
	}

	function Dashboard() {
		const [data, setData] = useState(null);
		const [loading, setLoading] = useState(true);
		const [error, setError] = useState(null);

		useEffect(function () {
			apiFetch({ path: '/parish/v1/dashboard' })
				.then(function (res) { setData(res); setLoading(false); })
				.catch(function (err) { setError(err.message || 'Failed to load dashboard'); setLoading(false); });
		}, []);

		if (loading) return el(LoadingSpinner, { text: 'Loading dashboard...' });
		if (error) return el(Notice, { status: 'error', isDismissible: false }, error);

		const features = data.enabled_features || {};
		const parishName = data.parish_name || 'Parish';
		const stats = data.stats || {};
		const readings = data.mass_reading_details || {};
		const upcomingEvents = data.upcoming_events || [];
		const nextMassTimes = data.next_mass_times || [];
		const recentNewsletters = data.recent_newsletters || [];

		return el('div', { className: 'parish-dashboard' },
			// Banner image (if configured)
			data.banner_image && el('div', { className: 'dashboard-banner' },
				el('img', { src: data.banner_image, alt: parishName })
			),

			// Header with logo, title, and description
			el('div', { className: 'dashboard-header' },
				el(Flex, { align: 'center', gap: 4 },
					data.parish_logo && el('img', { src: data.parish_logo, alt: 'Logo', className: 'parish-logo' }),
					el(FlexBlock, null,
						el('h1', null, parishName),
						data.parish_description && el('p', { className: 'parish-description' }, data.parish_description),
						data.diocese_name && el('p', { className: 'diocese-link' },
							data.diocese_url
								? el(Fragment, null, 'Diocese of ', el('a', { href: data.diocese_url, target: '_blank' }, data.diocese_name))
								: el(Fragment, null, 'Diocese of ', data.diocese_name)
						)
					)
				)
			),

			// Main grid with two columns
			el('div', { className: 'dashboard-grid' },
				// Left column - Main content (2fr)
				el('div', { className: 'dashboard-main' },
					// Secretary Priorities / Content Overview
					el(SecretaryPrioritiesCard, { stats: stats, features: features }),

					// Quick Actions
					el(QuickActionsCard, { features: features }),

					// Upcoming Events
					el(UpcomingEventsCard, { events: upcomingEvents, features: features }),

					// Mass Times
					el(MassTimesCard, { massTimes: nextMassTimes, features: features })
				),

				// Right column - Sidebar (1fr)
				el('div', { className: 'dashboard-sidebar' },
					// Liturgical Info
					features.liturgical && data.liturgical && el(LiturgicalCard, { liturgical: data.liturgical }),

					// Today's Readings
					el(ReadingsCard, { readings: readings, features: features }),

					// Recent Newsletters
					el(RecentNewslettersCard, { newsletters: recentNewsletters, features: features }),

					// Daily Reflection
					el(ReflectionCard, { reflection: data.reflection, features: features })
				)
			)
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, { Dashboard, LiturgicalCard });
})(window);
