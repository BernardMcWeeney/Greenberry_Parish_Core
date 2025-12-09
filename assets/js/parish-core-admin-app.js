/**
 * Parish Core Admin - App router & bootstrap
 */
(function (window) {
	'use strict';

	const { el, render, config } = window.ParishCoreAdmin;
	const {
		Dashboard,
		AboutParish,
		MassTimes,
		EventsCalendar,
		ReadingsAPI,
		Settings,
	} = window.ParishCoreAdmin;

	function App() {
		const page = config.page || 'dashboard';

		switch (page) {
			case 'dashboard':
				return el(Dashboard);
			case 'about':
				return el(AboutParish);
			case 'mass-times':
				return el(MassTimes);
			case 'events':
				return el(EventsCalendar);
			case 'readings':
				return el(ReadingsAPI);
			case 'settings':
				return el(Settings);
			default:
				return el('p', null, 'Unknown page: ' + page);
		}
	}

	function init() {
		var roots = [
			'parish-dashboard-app',
			'parish-about-app',
			'parish-mass-times-app',
			'parish-events-app',
			'parish-readings-app',
			'parish-settings-app',
		];

		roots.forEach(function (id) {
			var root = document.getElementById(id);
			if (root) {
				render(el(App), root);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window);
