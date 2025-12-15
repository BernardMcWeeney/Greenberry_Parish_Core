/**
 * Parish Core Admin - App router & bootstrap
 */
(function (window) {
	'use strict';

	var ParishCoreAdmin = window.ParishCoreAdmin;
	
	if (!ParishCoreAdmin || !ParishCoreAdmin.el || !ParishCoreAdmin.render) {
		console.error('ParishCoreAdmin not loaded');
		return;
	}

	var el = ParishCoreAdmin.el;
	var render = ParishCoreAdmin.render;
	var config = ParishCoreAdmin.config || {};

	function App() {
		var page = config.page || 'dashboard';

		// Get components from ParishCoreAdmin
		var Dashboard = ParishCoreAdmin.Dashboard;
		var AboutParish = ParishCoreAdmin.AboutParish;
		var MassTimes = ParishCoreAdmin.MassTimes;
		var EventsCalendar = ParishCoreAdmin.EventsCalendar;
		var SliderSettings = ParishCoreAdmin.SliderSettings;
		var ReadingsAPI = ParishCoreAdmin.ReadingsAPI;
		var Settings = ParishCoreAdmin.Settings;

		switch (page) {
			case 'dashboard':
				return Dashboard ? el(Dashboard) : el('p', null, 'Loading Dashboard...');
			case 'about':
				return AboutParish ? el(AboutParish) : el('p', null, 'Loading About Parish...');
			case 'mass-times':
				return MassTimes ? el(MassTimes) : el('p', null, 'Loading Mass Times...');
			case 'events':
				return EventsCalendar ? el(EventsCalendar) : el('p', null, 'Loading Events...');
			case 'slider':
				return SliderSettings ? el(SliderSettings) : el('p', null, 'Loading Slider Settings...');
			case 'readings':
				return ReadingsAPI ? el(ReadingsAPI) : el('p', null, 'Loading Readings API...');
			case 'settings':
				return Settings ? el(Settings) : el('p', null, 'Loading Settings...');
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
			'parish-slider-app',
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
