/**
 * Parish Core Admin - Shared utilities and globals
 */
(function (window) {
	'use strict';

	if (!window.wp || !window.wp.element || !window.wp.components || !window.wp.apiFetch) {
		return;
	}

	const {
		createElement: el,
		render,
		useState,
		useEffect,
		Fragment,
	} = wp.element;

	const {
		Panel,
		PanelBody,
		TextControl,
		TextareaControl,
		ToggleControl,
		Button,
		Spinner,
		Notice,
		Card,
		CardHeader,
		CardBody,
		CardFooter,
		Flex,
		FlexItem,
		FlexBlock,
		SelectControl,
		BaseControl,
		Modal,
		TabPanel,
	} = wp.components;

	const apiFetch = wp.apiFetch;
	const config = window.parishCore || {};
	const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

	function LoadingSpinner(props) {
		return el(
			'div',
			{ className: 'parish-loading' },
			el(Spinner),
			props.text && el('p', null, props.text)
		);
	}

	function generateId() {
		return 'id_' + Math.random().toString(36).substr(2, 9);
	}

	function MediaUploader(props) {
		const label = props.label;
		const value = props.value || {};
		const onChange = props.onChange;

		const openMedia = function () {
			const frame = wp.media({ title: label, multiple: false, library: { type: 'image' } });
			frame.on('select', function () {
				const att = frame.state().get('selection').first().toJSON();
				onChange(att.id, att.url);
			});
			frame.open();
		};

		return el(
			BaseControl,
			{ label: label, className: 'parish-media-uploader' },
			el(
				'div',
				{ className: 'media-preview' },
				value.url && el('img', { src: value.url, alt: label, className: 'preview-image' }),
				el(
					Flex,
					{ gap: 2, className: 'media-buttons' },
					el(
						Button,
						{ isSecondary: true, onClick: openMedia },
						value.url ? 'Change' : 'Select'
					),
					value.url &&
						el(
							Button,
							{
								isDestructive: true,
								isSmall: true,
								onClick: function () {
									onChange(0, '');
								},
							},
							'Remove'
						)
				)
			)
		);
	}

	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		// React glue
		el,
		render,
		useState,
		useEffect,
		Fragment,

		// WP components
		Panel,
		PanelBody,
		TextControl,
		TextareaControl,
		ToggleControl,
		Button,
		Spinner,
		Notice,
		Card,
		CardHeader,
		CardBody,
		CardFooter,
		Flex,
		FlexItem,
		FlexBlock,
		SelectControl,
		BaseControl,
		Modal,
		TabPanel,

		// WP data
		apiFetch,
		config,
		days,

		// Helpers
		LoadingSpinner,
		generateId,
		MediaUploader,
	});
})(window);
