(function (window) {
	'use strict';
	const { el, useState, useEffect, Fragment, Card, CardHeader, CardBody, CardFooter, Flex, FlexBlock, Button, Notice, LoadingSpinner, apiFetch, config, days } = window.ParishCoreAdmin;

	const CONTENT_FEATURES = [
		{ feature: 'death_notices', label: 'Death Notices', postType: 'parish_death_notice' },
		{ feature: 'baptism_notices', label: 'Baptism Notices', postType: 'parish_baptism' },
		{ feature: 'wedding_notices', label: 'Wedding Notices', postType: 'parish_wedding' },
		{ feature: 'churches', label: 'Churches', postType: 'parish_church' },
		{ feature: 'schools', label: 'Schools', postType: 'parish_school' },
		{ feature: 'cemeteries', label: 'Cemeteries', postType: 'parish_cemetery' },
		{ feature: 'groups', label: 'Parish Groups', postType: 'parish_group' },
		{ feature: 'newsletters', label: 'Newsletters', postType: 'parish_newsletter' },
		{ feature: 'news', label: 'Parish News', postType: 'parish_news' },
		{ feature: 'gallery', label: 'Gallery', postType: 'parish_gallery' },
		{ feature: 'reflections', label: 'Reflections', postType: 'parish_reflection' },
		{ feature: 'prayers', label: 'Prayers', postType: 'parish_prayer' },
	];

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

	function Dashboard() {
		const [data, setData] = useState(null);
		const [loading, setLoading] = useState(true);
		const [error, setError] = useState(null);

		useEffect(function () {
			apiFetch({ path: '/parish/v1/dashboard' })
				.then(function (res) { setData(res); setLoading(false); })
				.catch(function (err) { setError(err.message || 'Failed to load'); setLoading(false); });
		}, []);

		if (loading) return el(LoadingSpinner, { text: 'Loading dashboard...' });
		if (error) return el(Notice, { status: 'error', isDismissible: false }, error);

		const features = data.enabled_features || {};
		const parishName = data.parish_name || 'Parish';
		const today = new Date().toLocaleDateString('en-IE', { weekday: 'long' });
		const stats = data.stats || {};
		const overviewItems = CONTENT_FEATURES.filter(function (def) { return features[def.feature]; });
		const readingDetails = data.mass_reading_details || {};
		const recentNewsletters = (data.recent_newsletters || []).slice(0, 3);

		return el('div', { className: 'parish-dashboard' },
			data.parish_banner && el('div', { className: 'dashboard-banner' }, el('img', { src: data.parish_banner, alt: parishName })),
			el('div', { className: 'dashboard-header' },
				el(Flex, { align: 'center', gap: 4 },
					data.parish_logo && el('img', { src: data.parish_logo, alt: 'Logo', className: 'parish-logo' }),
					el(FlexBlock, null,
						el('h1', null, 'Welcome to ' + parishName),
						data.diocese_name && el('p', { className: 'diocese-link' },
							data.diocese_url
								? el('a', { href: data.diocese_url, target: '_blank' }, 'Diocese of ' + data.diocese_name + ' ↗')
								: 'Diocese of ' + data.diocese_name
						)
					)
				)
			),
			el(Card, { className: 'dashboard-card' },
				el(CardHeader, null, el('strong', null, 'Quick Actions')),
				el(CardBody, null,
					el('div', { className: 'quick-actions-grid' },
						(data.quick_actions || []).map(function (action, i) {
							return el('a', { key: i, href: action.url, className: 'quick-action-item' },
								el('span', { className: 'dashicons dashicons-' + (action.icon || 'plus-alt') }),
								el('span', null, action.label)
							);
						})
					)
				)
			),
			el('div', { className: 'dashboard-grid' },
				el('div', { className: 'dashboard-main' },
					el(Card, { className: 'dashboard-card' },
						el(CardHeader, null, el('strong', null, 'Content Overview')),
						el(CardBody, null,
							el('div', { className: 'stats-grid' },
								overviewItems.map(function (def) {
									var stat = def.postType && stats[def.postType] ? stats[def.postType] : null;
									var count = stat ? stat.published : null;
									var draft = stat ? stat.draft : 0;
									var inner = el(Fragment, null,
										el('div', { className: 'stat-count' }, count !== null ? count : '—'),
										el('div', { className: 'stat-label' }, def.label),
										draft > 0 && el('div', { className: 'stat-draft' }, draft + ' drafts'),
										def.postType && el('span', { className: 'stat-add' }, '+ Add')
									);
									if (def.postType) {
										return el('a', { key: def.feature, href: config.adminUrl + 'edit.php?post_type=' + def.postType, className: 'stat-item-link' }, inner);
									}
									return el('div', { key: def.feature, className: 'stat-item-link stat-item-link--nolink' }, inner);
								})
							)
						)
					)
				),
				el('div', { className: 'dashboard-sidebar' },
					features.liturgical && data.liturgical && el(LiturgicalCard, { liturgical: data.liturgical }),
					features.reflections && data.reflection && el(Card, { className: 'dashboard-card reflection-card' },
						el(CardHeader, null, el('strong', null, "Today's Reflection")),
						el(CardBody, null,
							el('blockquote', null, '"' + (data.reflection.content || '').substring(0, 200) + '"'),
							el('cite', null, '— ' + data.reflection.title)
						),
						el(CardFooter, null,
							el('a', { href: config.adminUrl + 'post-new.php?post_type=parish_reflection' }, 'Add a new reflection')
						)
					),
					features.liturgical && data.mass_reading_details && el(Card, { className: 'dashboard-card readings-card' },
						el(CardHeader, null, el('strong', null, "Today's Readings")),
						el(CardBody, null,
							el('div', { className: 'readings-list' },
								readingDetails.first_reading && el('p', null, el('strong', null, 'First Reading: '), String(readingDetails.first_reading)),
								readingDetails.psalm && el('p', null, el('strong', null, 'Psalm: '), String(readingDetails.psalm)),
								readingDetails.second_reading && el('p', null, el('strong', null, 'Second Reading: '), String(readingDetails.second_reading)),
								readingDetails.gospel_acclamation && el('p', null, el('strong', null, 'Gospel Acclamation: '), String(readingDetails.gospel_acclamation)),
								readingDetails.gospel && el('p', null, el('strong', null, 'Gospel: '), String(readingDetails.gospel))
							)
						)
					),
					features.newsletters && recentNewsletters.length > 0 && el(Card, { className: 'dashboard-card' },
						el(CardHeader, null, el('strong', null, 'Recent Newsletters')),
						el(CardBody, null,
							el('ul', { className: 'recent-items-list' },
								recentNewsletters.map(function (item) {
									return el('li', { key: item.id },
										el('a', { href: item.edit_url, className: 'item-title' }, item.title),
										el('span', { className: 'item-date' }, item.date)
									);
								})
							)
						),
						el(CardFooter, null,
							el('a', { href: config.adminUrl + 'post-new.php?post_type=parish_newsletter' }, 'Add a new newsletter')
						)
					)
				)
			)
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, { Dashboard, LiturgicalCard });
})(window);
