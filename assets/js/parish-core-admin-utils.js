/**
 * Parish Core Admin - Shared utilities and globals
 */
(function (window) {
	'use strict';

	if (!window.wp || !window.wp.element || !window.wp.components || !window.wp.apiFetch) {
		console.warn('Parish Core Admin: Required WordPress dependencies not loaded');
		return;
	}

	var wpElement = window.wp.element;
	var wpComponents = window.wp.components;

	var el = wpElement.createElement;
	var render = wpElement.render;
	var useState = wpElement.useState;
	var useEffect = wpElement.useEffect;
	var useCallback = wpElement.useCallback;
	var useMemo = wpElement.useMemo;
	var useRef = wpElement.useRef;
	var Fragment = wpElement.Fragment;

	var Panel = wpComponents.Panel;
	var PanelBody = wpComponents.PanelBody;
	var TextControl = wpComponents.TextControl;
	var TextareaControl = wpComponents.TextareaControl;
	var ToggleControl = wpComponents.ToggleControl;
	var Button = wpComponents.Button;
	var Spinner = wpComponents.Spinner;
	var Notice = wpComponents.Notice;
	var Card = wpComponents.Card;
	var CardHeader = wpComponents.CardHeader;
	var CardBody = wpComponents.CardBody;
	var CardFooter = wpComponents.CardFooter;
	var Flex = wpComponents.Flex;
	var FlexItem = wpComponents.FlexItem;
	var FlexBlock = wpComponents.FlexBlock;
	var SelectControl = wpComponents.SelectControl;
	var BaseControl = wpComponents.BaseControl;
	var Modal = wpComponents.Modal;
	var TabPanel = wpComponents.TabPanel;
	var Popover = wpComponents.Popover;
	var DropdownMenu = wpComponents.DropdownMenu;
	var CheckboxControl = wpComponents.CheckboxControl;
	var DateTimePicker = wpComponents.DateTimePicker;

	var apiFetch = window.wp.apiFetch;
	var config = window.parishCore || {};
	var days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

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
		var label = props.label;
		var value = props.value || {};
		var onChange = props.onChange;

		var openMedia = function () {
			var frame = wp.media({ title: label, multiple: false, library: { type: 'image' } });
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
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

	// Export everything to ParishCoreAdmin
	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	Object.assign(window.ParishCoreAdmin, {
		// React glue
		el: el,
		render: render,
		useState: useState,
		useEffect: useEffect,
		useCallback: useCallback,
		useMemo: useMemo,
		useRef: useRef,
		Fragment: Fragment,

		// WP components
		Panel: Panel,
		PanelBody: PanelBody,
		TextControl: TextControl,
		TextareaControl: TextareaControl,
		ToggleControl: ToggleControl,
		Button: Button,
		Spinner: Spinner,
		Notice: Notice,
		Card: Card,
		CardHeader: CardHeader,
		CardBody: CardBody,
		CardFooter: CardFooter,
		Flex: Flex,
		FlexItem: FlexItem,
		FlexBlock: FlexBlock,
		SelectControl: SelectControl,
		BaseControl: BaseControl,
		Modal: Modal,
		TabPanel: TabPanel,
		Popover: Popover,
		DropdownMenu: DropdownMenu,
		CheckboxControl: CheckboxControl,
		DateTimePicker: DateTimePicker,

		// WP data
		apiFetch: apiFetch,
		config: config,
		days: days,

		// Helpers
		LoadingSpinner: LoadingSpinner,
		generateId: generateId,
		MediaUploader: MediaUploader,
	});
})(window);
