/**
 * Parish Core Admin - Slider Management
 * Full-featured slider settings with rosary images, CTA colors, and all dynamic sources
 */
(function (window) {
	'use strict';

	var ParishCoreAdmin = window.ParishCoreAdmin;
	if (!ParishCoreAdmin) {
		console.error('ParishCoreAdmin not loaded');
		return;
	}

	var el = ParishCoreAdmin.el;
	var useState = ParishCoreAdmin.useState;
	var useEffect = ParishCoreAdmin.useEffect;
	var Fragment = ParishCoreAdmin.Fragment;
	var Panel = ParishCoreAdmin.Panel;
	var PanelBody = ParishCoreAdmin.PanelBody;
	var TextControl = ParishCoreAdmin.TextControl;
	var TextareaControl = ParishCoreAdmin.TextareaControl;
	var ToggleControl = ParishCoreAdmin.ToggleControl;
	var SelectControl = ParishCoreAdmin.SelectControl;
	var Button = ParishCoreAdmin.Button;
	var Notice = ParishCoreAdmin.Notice;
	var Modal = ParishCoreAdmin.Modal;
	var Spinner = ParishCoreAdmin.Spinner;
	var apiFetch = ParishCoreAdmin.apiFetch;
	var generateId = ParishCoreAdmin.generateId;

	// Loading spinner component
	function LoadingSpinner(props) {
		return el(
			'div',
			{ className: 'parish-loading', style: { textAlign: 'center', padding: '40px' } },
			el(Spinner),
			props.text && el('p', null, props.text)
		);
	}

	// Dashicon component
	function Dashicon(props) {
		return el('span', { className: 'dashicons dashicons-' + props.icon });
	}

	// Range control component
	function RangeControl(props) {
		return el(
			'div',
			{ className: 'parish-range-control', style: { marginBottom: '16px' } },
			el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '500' } }, props.label),
			el(
				'div',
				{ style: { display: 'flex', alignItems: 'center', gap: '12px' } },
				el('input', {
					type: 'range',
					min: props.min || 0,
					max: props.max || 100,
					step: props.step || 1,
					value: props.value,
					onChange: function (e) {
						props.onChange(parseInt(e.target.value, 10));
					},
					style: { flex: 1 },
				}),
				el('span', { style: { minWidth: '60px', textAlign: 'right', fontSize: '13px' } }, props.value + (props.suffix || ''))
			),
			props.help && el('p', { style: { margin: '4px 0 0', color: '#757575', fontSize: '12px' } }, props.help)
		);
	}

	// Color picker component
	function ColorPicker(props) {
		return el(
			'div',
			{ style: { marginBottom: '16px' } },
			el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '500' } }, props.label),
			el(
				'div',
				{ style: { display: 'flex', gap: '8px', alignItems: 'center' } },
				el('input', {
					type: 'color',
					value: props.value || '#000000',
					onChange: function (e) {
						props.onChange(e.target.value);
					},
					style: { width: '50px', height: '34px', padding: '2px', border: '1px solid #ddd', borderRadius: '4px', cursor: 'pointer' },
				}),
				el('input', {
					type: 'text',
					value: props.value || '',
					onChange: function (e) {
						props.onChange(e.target.value);
					},
					style: { width: '100px', padding: '6px 8px', border: '1px solid #ddd', borderRadius: '4px' },
					placeholder: '#000000',
				})
			),
			props.help && el('p', { style: { margin: '4px 0 0', color: '#757575', fontSize: '12px' } }, props.help)
		);
	}

	// Array move helper for drag and drop
	function arrayMove(arr, fromIndex, toIndex) {
		var newArr = arr.slice();
		var element = newArr.splice(fromIndex, 1)[0];
		newArr.splice(toIndex, 0, element);
		return newArr;
	}

	// Image Upload Field using native WordPress media
	function ImageUploadField(props) {
		var imageId = props.imageId;
		var imageUrl = props.imageUrl;
		var onSelect = props.onSelect;
		var onRemove = props.onRemove;
		var label = props.label;
		var help = props.help;
		var compact = props.compact;

		var hasImage = imageUrl || imageId;

		var openMediaLibrary = function () {
			var frame = wp.media({
				title: label || 'Select Image',
				multiple: false,
				library: { type: 'image' },
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				onSelect(attachment.id, attachment.url);
			});

			frame.open();
		};

		if (compact) {
			return el(
				'div',
				{ className: 'image-upload-compact', style: { display: 'flex', alignItems: 'center', gap: '12px' } },
				el(
					'div',
					{
						style: {
							width: '80px',
							height: '60px',
							backgroundColor: '#f0f0f0',
							borderRadius: '4px',
							overflow: 'hidden',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							flexShrink: 0,
						},
					},
					hasImage
						? el('img', { src: imageUrl, alt: '', style: { width: '100%', height: '100%', objectFit: 'cover' } })
						: el(Dashicon, { icon: 'format-image' })
				),
				el(
					'div',
					{ style: { flex: 1 } },
					el('div', { style: { fontWeight: '500', marginBottom: '4px' } }, label),
					el(
						'div',
						{ style: { display: 'flex', gap: '8px' } },
						el(Button, { isSmall: true, isSecondary: true, onClick: openMediaLibrary }, hasImage ? 'Change' : 'Select'),
						hasImage && el(Button, { isSmall: true, isDestructive: true, onClick: onRemove }, 'Remove')
					)
				)
			);
		}

		return el(
			'div',
			{ className: 'image-upload-field', style: { marginBottom: '16px' } },
			label && el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '500' } }, label),
			hasImage
				? el(
						'div',
						{ className: 'image-preview', style: { marginBottom: '8px' } },
						el('img', {
							src: imageUrl,
							alt: '',
							style: { maxWidth: '200px', height: 'auto', display: 'block', marginBottom: '8px', borderRadius: '4px' },
						}),
						el(
							'div',
							{ style: { display: 'flex', gap: '8px' } },
							el(Button, { isSecondary: true, isSmall: true, onClick: openMediaLibrary }, 'Change'),
							el(Button, { isDestructive: true, isSmall: true, onClick: onRemove }, 'Remove')
						)
				  )
				: el(Button, { isSecondary: true, onClick: openMediaLibrary }, el(Dashicon, { icon: 'upload' }), ' Select Image'),
			help && el('p', { className: 'description', style: { marginTop: '4px', color: '#757575', fontSize: '12px' } }, help)
		);
	}

	// Source category labels
	var sourceCategories = {
		liturgical: { label: 'Liturgical', icon: 'calendar-alt' },
		content: { label: 'Content', icon: 'admin-post' },
		sacraments: { label: 'Sacraments', icon: 'heart' },
		places: { label: 'Places', icon: 'building' },
		community: { label: 'Community', icon: 'groups' },
	};

	// Slide Editor Modal
	function SlideEditorModal(props) {
		var slide = props.slide;
		var dynamicSources = props.dynamicSources;
		var onSave = props.onSave;
		var onClose = props.onClose;

		var _state = useState(Object.assign({}, slide));
		var editedSlide = _state[0];
		var setEditedSlide = _state[1];

		var updateField = function (key, value) {
			setEditedSlide(Object.assign({}, editedSlide, (_a = {}, _a[key] = value, _a)));
			var _a;
		};

		var handleSave = function () {
			onSave(editedSlide);
		};

		var isManual = editedSlide.type === 'manual';
		var isDynamic = editedSlide.type === 'dynamic';

		// Group sources by category
		var groupedSources = {};
		Object.keys(dynamicSources || {}).forEach(function (key) {
			var source = dynamicSources[key];
			var category = source.category || 'content';
			if (!groupedSources[category]) {
				groupedSources[category] = [];
			}
			groupedSources[category].push({ key: key, source: source });
		});

		var sourceOptions = [{ label: '-- Select Source --', value: '' }];
		Object.keys(groupedSources).forEach(function (category) {
			var catInfo = sourceCategories[category] || { label: category };
			sourceOptions.push({ label: '── ' + catInfo.label + ' ──', value: '', disabled: true });
			groupedSources[category].forEach(function (item) {
				sourceOptions.push({ label: item.source.name, value: item.key });
			});
		});

		return el(
			Modal,
			{
				title: slide.id ? 'Edit Slide' : 'Add New Slide',
				onRequestClose: onClose,
				className: 'parish-slider-modal',
				style: { maxWidth: '600px' },
			},
			el(
				'div',
				{ className: 'slide-editor', style: { maxHeight: '70vh', overflowY: 'auto' } },

				// Slide Type Selection
				el(
					'div',
					{
						className: 'slide-type-selector',
						style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginBottom: '24px' },
					},
					el(
						'div',
						{
							className: 'type-option' + (isManual ? ' is-selected' : ''),
							onClick: function () {
								updateField('type', 'manual');
							},
							style: {
								padding: '16px',
								border: isManual ? '2px solid #007cba' : '1px solid #ddd',
								borderRadius: '4px',
								cursor: 'pointer',
								backgroundColor: isManual ? '#f0f7fc' : '#fff',
								textAlign: 'center',
							},
						},
						el(Dashicon, { icon: 'edit' }),
						el('div', { style: { fontWeight: '600', marginTop: '8px' } }, 'Manual Slide'),
						el('small', { style: { color: '#757575' } }, 'Create custom content')
					),
					el(
						'div',
						{
							className: 'type-option' + (isDynamic ? ' is-selected' : ''),
							onClick: function () {
								updateField('type', 'dynamic');
							},
							style: {
								padding: '16px',
								border: isDynamic ? '2px solid #007cba' : '1px solid #ddd',
								borderRadius: '4px',
								cursor: 'pointer',
								backgroundColor: isDynamic ? '#f0f7fc' : '#fff',
								textAlign: 'center',
							},
						},
						el(Dashicon, { icon: 'update' }),
						el('div', { style: { fontWeight: '600', marginTop: '8px' } }, 'Dynamic Slide'),
						el('small', { style: { color: '#757575' } }, 'Auto-populated content')
					)
				),

				// Dynamic Source Selection
				isDynamic &&
					el(
						'div',
						{ style: { marginBottom: '24px', padding: '16px', backgroundColor: '#f6f7f7', borderRadius: '4px' } },
						el(SelectControl, {
							label: 'Select Data Source',
							value: editedSlide.source || '',
							options: sourceOptions,
							onChange: function (v) {
								updateField('source', v);
							},
						}),
						editedSlide.source &&
							dynamicSources[editedSlide.source] &&
							el(
								'p',
								{ style: { marginTop: '8px', color: '#666', fontSize: '13px' } },
								el(Dashicon, { icon: dynamicSources[editedSlide.source].icon || 'admin-post' }),
								' ',
								dynamicSources[editedSlide.source].description
							),
						el(
							'p',
							{
								style: {
									marginTop: '12px',
									padding: '8px',
									backgroundColor: '#fff',
									borderRadius: '4px',
									fontSize: '12px',
									color: '#666',
								},
							},
							el(Dashicon, { icon: 'info' }),
							' Fields below are optional overrides. Leave blank to use auto-generated content.'
						)
					),

				// Image Upload
				el(ImageUploadField, {
					label: 'Background Image',
					imageId: editedSlide.image_id,
					imageUrl: editedSlide.image_url,
					onSelect: function (id, url) {
						setEditedSlide(Object.assign({}, editedSlide, { image_id: id, image_url: url }));
					},
					onRemove: function () {
						setEditedSlide(Object.assign({}, editedSlide, { image_id: 0, image_url: '' }));
					},
					help: isDynamic ? 'If no image is set, the dynamic source may provide one.' : null,
				}),

				// Image Display Options
				el(
					'div',
					{ style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '16px' } },
					el(SelectControl, {
						label: 'Image Fit',
						value: editedSlide.image_fit || 'cover',
						options: [
							{ label: 'Cover (fill area)', value: 'cover' },
							{ label: 'Contain (show all)', value: 'contain' },
							{ label: 'Fill (stretch)', value: 'fill' },
						],
						onChange: function (v) {
							updateField('image_fit', v);
						},
					}),
					el(SelectControl, {
						label: 'Image Position',
						value: editedSlide.image_position || 'center',
						options: [
							{ label: 'Center', value: 'center' },
							{ label: 'Top', value: 'top' },
							{ label: 'Bottom', value: 'bottom' },
							{ label: 'Left', value: 'left' },
							{ label: 'Right', value: 'right' },
						],
						onChange: function (v) {
							updateField('image_position', v);
						},
					})
				),

				// Display Mode
				el(SelectControl, {
					label: 'Display Mode',
					value: editedSlide.display_mode || 'full',
					options: [
						{ label: 'Full (title, subtitle, description, button)', value: 'full' },
						{ label: 'Title Only (title + button)', value: 'title' },
						{ label: 'Image Only (no text)', value: 'image' },
					],
					onChange: function (v) {
						updateField('display_mode', v);
					},
					help: 'Choose what content to display on this slide',
				}),

				// Title (hidden if image only mode)
				editedSlide.display_mode !== 'image' &&
				el(TextControl, {
					label: isManual ? 'Title' : 'Title Override',
					value: isManual ? editedSlide.title || '' : editedSlide.title_override || '',
					onChange: function (v) {
						updateField(isManual ? 'title' : 'title_override', v);
					},
					placeholder: isDynamic ? 'Leave blank for auto-generated' : 'Enter title',
				}),

				// Subtitle (hidden if title only or image only)
				editedSlide.display_mode === 'full' &&
				el(TextControl, {
					label: isManual ? 'Subtitle' : 'Subtitle Override',
					value: isManual ? editedSlide.subtitle || '' : editedSlide.subtitle_override || '',
					onChange: function (v) {
						updateField(isManual ? 'subtitle' : 'subtitle_override', v);
					},
					placeholder: isDynamic ? 'Leave blank for auto-generated' : 'Enter subtitle',
				}),

				// Description (hidden if title only or image only)
				editedSlide.display_mode === 'full' &&
				el(TextareaControl, {
					label: isManual ? 'Description' : 'Description Override',
					value: isManual ? editedSlide.description || '' : editedSlide.description_override || '',
					onChange: function (v) {
						updateField(isManual ? 'description' : 'description_override', v);
					},
					rows: 2,
					placeholder: isDynamic ? 'Leave blank for auto-generated' : 'Enter description',
				}),

				// CTA Fields (hidden if image only)
				editedSlide.display_mode !== 'image' &&
				el(
					'div',
					{ style: { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' } },
					el(TextControl, {
						label: 'Button Text',
						value: editedSlide.cta_text || '',
						onChange: function (v) {
							updateField('cta_text', v);
						},
						placeholder: isDynamic ? 'Leave blank for default' : 'e.g., Learn More',
					}),
					el(TextControl, {
						label: 'Button Link',
						value: editedSlide.cta_link || '',
						onChange: function (v) {
							updateField('cta_link', v);
						},
						placeholder: isDynamic ? 'Leave blank for default' : 'https://',
					})
				),

				// Text Alignment
				el(SelectControl, {
					label: 'Text Alignment',
					value: editedSlide.text_align || 'left',
					options: [
						{ label: 'Left', value: 'left' },
						{ label: 'Center', value: 'center' },
						{ label: 'Right', value: 'right' },
					],
					onChange: function (v) {
						updateField('text_align', v);
					},
				}),

				// Actions
				el(
					'div',
					{ style: { marginTop: '24px', display: 'flex', justifyContent: 'flex-end', gap: '8px' } },
					el(Button, { isSecondary: true, onClick: onClose }, 'Cancel'),
					el(
						Button,
						{
							isPrimary: true,
							onClick: handleSave,
							disabled: isDynamic && !editedSlide.source,
						},
						slide.id ? 'Update Slide' : 'Add Slide'
					)
				)
			)
		);
	}

	// Slide Card Component
	function SlideCard(props) {
		var slide = props.slide;
		var index = props.index;
		var dynamicSources = props.dynamicSources;
		var onEdit = props.onEdit;
		var onDelete = props.onDelete;
		var onToggle = props.onToggle;
		var onMoveUp = props.onMoveUp;
		var onMoveDown = props.onMoveDown;
		var isFirst = props.isFirst;
		var isLast = props.isLast;

		var isManual = slide.type === 'manual';
		var source = (dynamicSources || {})[slide.source];

		return el(
			'div',
			{
				className: 'slide-card' + (slide.enabled === false ? ' is-disabled' : ''),
				style: {
					border: '1px solid #ddd',
					borderRadius: '4px',
					marginBottom: '12px',
					backgroundColor: slide.enabled === false ? '#f6f7f7' : '#fff',
					opacity: slide.enabled === false ? 0.6 : 1,
				},
			},
			// Header
			el(
				'div',
				{
					style: {
						display: 'flex',
						alignItems: 'center',
						justifyContent: 'space-between',
						padding: '10px 12px',
						borderBottom: '1px solid #eee',
						backgroundColor: '#fafafa',
					},
				},
				// Order controls
				el(
					'div',
					{ style: { display: 'flex', alignItems: 'center', gap: '4px' } },
					el(Button, { isSmall: true, disabled: isFirst, onClick: onMoveUp, 'aria-label': 'Move up' }, '↑'),
					el('span', { style: { padding: '0 8px', fontWeight: '600', minWidth: '24px', textAlign: 'center' } }, index + 1),
					el(Button, { isSmall: true, disabled: isLast, onClick: onMoveDown, 'aria-label': 'Move down' }, '↓')
				),
				// Type badge
				el(
					'span',
					{
						style: {
							padding: '3px 8px',
							borderRadius: '3px',
							fontSize: '11px',
							fontWeight: '500',
							backgroundColor: isManual ? '#e5f0fa' : '#e5fae5',
							color: isManual ? '#0073aa' : '#008a00',
						},
					},
					isManual ? 'Manual' : source?.name || 'Dynamic'
				),
				// Actions
				el(
					'div',
					{ style: { display: 'flex', alignItems: 'center', gap: '8px' } },
					el(ToggleControl, { checked: slide.enabled !== false, onChange: onToggle }),
					el(Button, { isSmall: true, onClick: onEdit }, 'Edit'),
					el(Button, { isSmall: true, isDestructive: true, onClick: onDelete }, '×')
				)
			),
			// Body
			el(
				'div',
				{ style: { display: 'flex', padding: '12px', gap: '16px' } },
				// Thumbnail
				el(
					'div',
					{
						style: {
							width: '100px',
							height: '70px',
							backgroundColor: '#f0f0f0',
							borderRadius: '4px',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							overflow: 'hidden',
							flexShrink: 0,
						},
					},
					slide.image_url
						? el('img', { src: slide.image_url, alt: '', style: { width: '100%', height: '100%', objectFit: 'cover' } })
						: el('span', { style: { color: '#999', fontSize: '11px' } }, isManual ? 'No image' : 'Auto')
				),
				// Info
				el(
					'div',
					{ style: { flex: 1, minWidth: 0 } },
					el(
						'h4',
						{ style: { margin: '0 0 4px 0', fontSize: '14px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' } },
						isManual ? slide.title || '(No title)' : slide.title_override || '(Auto-generated)'
					),
					el(
						'p',
						{ style: { margin: 0, color: '#666', fontSize: '12px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' } },
						isManual ? slide.subtitle || '' : slide.subtitle_override || source?.description || ''
					)
				)
			)
		);
	}

	// Rosary Images Settings Component
	function RosaryImagesSettings(props) {
		var rosaryImages = props.rosaryImages || {};
		var onChange = props.onChange;

		var mysteries = ['Joyful', 'Sorrowful', 'Glorious', 'Luminous'];
		var descriptions = {
			Joyful: 'Used when Joyful Mysteries are prayed (Mon, Sat, Advent/Christmas Sun)',
			Sorrowful: 'Used when Sorrowful Mysteries are prayed (Tue, Fri, Lent Sun)',
			Glorious: 'Used when Glorious Mysteries are prayed (Wed, Sun)',
			Luminous: 'Used when Luminous Mysteries are prayed (Thursday)',
		};

		var updateImage = function (mystery, id, url) {
			var updated = Object.assign({}, rosaryImages);
			updated[mystery] = { id: id, url: url };
			onChange(updated);
		};

		var removeImage = function (mystery) {
			var updated = Object.assign({}, rosaryImages);
			updated[mystery] = { id: 0, url: '' };
			onChange(updated);
		};

		return el(
			'div',
			{ className: 'rosary-images-settings' },
			el('p', { style: { marginBottom: '16px', color: '#666' } }, 'Set custom background images for each rosary mystery series. These will be used when the "Today\'s Rosary" dynamic slide is active.'),
			mysteries.map(function (mystery) {
				var image = rosaryImages[mystery] || {};
				return el(
					'div',
					{
						key: mystery,
						style: { marginBottom: '16px', padding: '12px', backgroundColor: '#f9f9f9', borderRadius: '4px' },
					},
					el(ImageUploadField, {
						label: mystery + ' Mysteries',
						imageId: image.id,
						imageUrl: image.url,
						onSelect: function (id, url) {
							updateImage(mystery, id, url);
						},
						onRemove: function () {
							removeImage(mystery);
						},
						compact: true,
					}),
					el('p', { style: { margin: '8px 0 0 92px', fontSize: '11px', color: '#888' } }, descriptions[mystery])
				);
			})
		);
	}

	// Season Images Settings Component
	function SeasonImagesSettings(props) {
		var seasonImages = props.seasonImages || {};
		var onChange = props.onChange;

		var seasons = ['Advent', 'Christmas', 'Lent', 'Easter', 'Ordinary Time'];
		var descriptions = {
			'Advent': 'Purple season - 4 Sundays before Christmas',
			'Christmas': 'White season - Christmas Day to Baptism of Jesus',
			'Lent': 'Purple season - Ash Wednesday to Holy Saturday',
			'Easter': 'White season - Easter Sunday to Pentecost',
			'Ordinary Time': 'Green season - time outside special seasons',
		};
		var colors = {
			'Advent': '#8B008B',
			'Christmas': '#FFD700',
			'Lent': '#8B008B',
			'Easter': '#FFD700',
			'Ordinary Time': '#008000',
		};

		var updateImage = function (season, id, url) {
			var updated = Object.assign({}, seasonImages);
			updated[season] = { id: id, url: url };
			onChange(updated);
		};

		var removeImage = function (season) {
			var updated = Object.assign({}, seasonImages);
			updated[season] = { id: 0, url: '' };
			onChange(updated);
		};

		return el(
			'div',
			{ className: 'season-images-settings' },
			el('p', { style: { marginBottom: '16px', color: '#666' } }, 'Set custom background images for each liturgical season. These will be used when the "Liturgical Season" dynamic slide is active.'),
			seasons.map(function (season) {
				var image = seasonImages[season] || {};
				return el(
					'div',
					{
						key: season,
						style: { marginBottom: '16px', padding: '12px', backgroundColor: '#f9f9f9', borderRadius: '4px' },
					},
					el(
						'div',
						{ style: { display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' } },
						el('span', { style: { width: '12px', height: '12px', borderRadius: '50%', backgroundColor: colors[season], flexShrink: 0 } }),
						el('strong', null, season)
					),
					el(ImageUploadField, {
						imageId: image.id,
						imageUrl: image.url,
						onSelect: function (id, url) {
							updateImage(season, id, url);
						},
						onRemove: function () {
							removeImage(season);
						},
						compact: true,
					}),
					el('p', { style: { margin: '8px 0 0 92px', fontSize: '11px', color: '#888' } }, descriptions[season])
				);
			})
		);
	}

	// Main Slider Settings Component
	function SliderSettings() {
		var _state1 = useState(null);
		var settings = _state1[0];
		var setSettings = _state1[1];

		var _state2 = useState({});
		var dynamicSources = _state2[0];
		var setDynamicSources = _state2[1];

		var _state3 = useState(true);
		var loading = _state3[0];
		var setLoading = _state3[1];

		var _state4 = useState(false);
		var saving = _state4[0];
		var setSaving = _state4[1];

		var _state5 = useState(null);
		var notice = _state5[0];
		var setNotice = _state5[1];

		var _state6 = useState(null);
		var editingSlide = _state6[0];
		var setEditingSlide = _state6[1];

		var _state7 = useState(false);
		var showAddModal = _state7[0];
		var setShowAddModal = _state7[1];

		useEffect(function () {
			Promise.all([apiFetch({ path: '/parish/v1/slider/settings' }), apiFetch({ path: '/parish/v1/slider/sources' })])
				.then(function (res) {
					setSettings(res[0] || { enabled: true, slides: [] });
					setDynamicSources(res[1] || {});
					setLoading(false);
				})
				.catch(function (err) {
					console.error('Slider load error:', err);
					setSettings({ enabled: true, slides: [] });
					setDynamicSources({});
					setLoading(false);
					setNotice({ type: 'error', message: 'Failed to load slider settings: ' + (err.message || 'Unknown error') });
				});
		}, []);

		var updateSetting = function (key, value) {
			var updated = {};
			updated[key] = value;
			setSettings(Object.assign({}, settings, updated));
		};

		var save = function () {
			setSaving(true);
			apiFetch({
				path: '/parish/v1/slider/settings',
				method: 'POST',
				data: settings,
			})
				.then(function () {
					setSaving(false);
					setNotice({ type: 'success', message: 'Slider settings saved!' });
				})
				.catch(function (err) {
					setSaving(false);
					setNotice({ type: 'error', message: err.message || 'Failed to save' });
				});
		};

		var addSlide = function (slide) {
			var newSlide = Object.assign({ id: generateId(), enabled: true }, slide);
			var slides = (settings.slides || []).concat([newSlide]);
			updateSetting('slides', slides);
			setShowAddModal(false);
		};

		var updateSlide = function (updatedSlide) {
			var slides = (settings.slides || []).map(function (s) {
				return s.id === updatedSlide.id ? updatedSlide : s;
			});
			updateSetting('slides', slides);
			setEditingSlide(null);
		};

		var deleteSlide = function (slideId) {
			if (!confirm('Delete this slide?')) return;
			var slides = (settings.slides || []).filter(function (s) {
				return s.id !== slideId;
			});
			updateSetting('slides', slides);
		};

		var toggleSlide = function (slideId) {
			var slides = (settings.slides || []).map(function (s) {
				if (s.id === slideId) {
					return Object.assign({}, s, { enabled: !s.enabled });
				}
				return s;
			});
			updateSetting('slides', slides);
		};

		var moveSlide = function (fromIndex, toIndex) {
			var slides = arrayMove(settings.slides || [], fromIndex, toIndex);
			updateSetting('slides', slides);
		};

		if (loading) {
			return el(LoadingSpinner, { text: 'Loading slider settings...' });
		}

		var slides = (settings && settings.slides) || [];
		var sourceCount = Object.keys(dynamicSources).length;

		return el(
			'div',
			{ className: 'parish-slider-settings' },

			// Notice
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

			// Description
			el(
				'p',
				{ className: 'description', style: { marginBottom: '20px' } },
				'Manage your homepage hero slider with ',
				el('strong', null, sourceCount),
				' dynamic content sources available.'
			),

			el(
				Panel,
				null,

				// Slides Management - First for prominence
				el(
					PanelBody,
					{ title: 'Slides (' + slides.length + ')', initialOpen: true },
					el(
						'div',
						{ style: { marginBottom: '16px' } },
						el(
							Button,
							{
								isPrimary: true,
								onClick: function () {
									setShowAddModal(true);
								},
							},
							'+ Add Slide'
						)
					),
					slides.length === 0
						? el(
								'div',
								{
									style: {
										padding: '40px',
										textAlign: 'center',
										backgroundColor: '#f6f7f7',
										borderRadius: '4px',
										border: '2px dashed #ddd',
									},
								},
								el(Dashicon, { icon: 'images-alt2' }),
								el('p', { style: { margin: '12px 0 0' } }, 'No slides yet. Add your first slide to get started.'),
								el('p', { style: { margin: '8px 0 0', fontSize: '12px', color: '#666' } }, sourceCount + ' dynamic sources available')
						  )
						: el(
								'div',
								{ className: 'slides-list' },
								slides.map(function (slide, index) {
									return el(SlideCard, {
										key: slide.id,
										slide: slide,
										index: index,
										dynamicSources: dynamicSources,
										isFirst: index === 0,
										isLast: index === slides.length - 1,
										onEdit: function () {
											setEditingSlide(slide);
										},
										onDelete: function () {
											deleteSlide(slide.id);
										},
										onToggle: function () {
											toggleSlide(slide.id);
										},
										onMoveUp: function () {
											moveSlide(index, index - 1);
										},
										onMoveDown: function () {
											moveSlide(index, index + 1);
										},
									});
								})
						  )
				),

				// General Settings
				el(
					PanelBody,
					{ title: 'Slider Settings', initialOpen: false },
					el(ToggleControl, {
						label: 'Enable Slider',
						checked: settings.enabled !== false,
						onChange: function (v) {
							updateSetting('enabled', v);
						},
					}),
					el(ToggleControl, {
						label: 'Autoplay',
						checked: settings.autoplay !== false,
						onChange: function (v) {
							updateSetting('autoplay', v);
						},
					}),
					settings.autoplay !== false &&
						el(RangeControl, {
							label: 'Autoplay Speed',
							value: settings.autoplay_speed || 5000,
							onChange: function (v) {
								updateSetting('autoplay_speed', v);
							},
							min: 2000,
							max: 15000,
							step: 500,
							suffix: 'ms',
						}),
					el(RangeControl, {
						label: 'Transition Speed',
						value: settings.transition_speed || 1000,
						onChange: function (v) {
							updateSetting('transition_speed', v);
						},
						min: 300,
						max: 2000,
						step: 100,
						suffix: 'ms',
					}),
					el(ToggleControl, {
						label: 'Show Navigation Arrows',
						checked: settings.show_arrows !== false,
						onChange: function (v) {
							updateSetting('show_arrows', v);
						},
					}),
					el(ToggleControl, {
						label: 'Show Navigation Dots',
						checked: settings.show_dots !== false,
						onChange: function (v) {
							updateSetting('show_dots', v);
						},
					}),
					el(ToggleControl, {
						label: 'Pause on Hover',
						checked: settings.pause_on_hover !== false,
						onChange: function (v) {
							updateSetting('pause_on_hover', v);
						},
					})
				),

				// Appearance Settings
				el(
					PanelBody,
					{ title: 'Appearance', initialOpen: false },
					el(RangeControl, {
						label: 'Height - Desktop',
						value: settings.height_desktop || 700,
						onChange: function (v) {
							updateSetting('height_desktop', v);
						},
						min: 400,
						max: 1000,
						step: 50,
						suffix: 'px',
					}),
					el(RangeControl, {
						label: 'Height - Tablet',
						value: settings.height_tablet || 500,
						onChange: function (v) {
							updateSetting('height_tablet', v);
						},
						min: 300,
						max: 800,
						step: 50,
						suffix: 'px',
					}),
					el(RangeControl, {
						label: 'Height - Mobile',
						value: settings.height_mobile || 400,
						onChange: function (v) {
							updateSetting('height_mobile', v);
						},
						min: 250,
						max: 600,
						step: 50,
						suffix: 'px',
					}),
					el(ColorPicker, {
						label: 'Overlay Color',
						value: settings.overlay_color || '#4A8391',
						onChange: function (v) {
							updateSetting('overlay_color', v);
						},
					}),
					el(RangeControl, {
						label: 'Overlay Opacity',
						value: Math.round((settings.overlay_opacity || 0.7) * 100),
						onChange: function (v) {
							updateSetting('overlay_opacity', v / 100);
						},
						min: 0,
						max: 100,
						step: 5,
						suffix: '%',
					}),
					el(ToggleControl, {
						label: 'Use Gradient Overlay',
						checked: settings.overlay_gradient !== false,
						onChange: function (v) {
							updateSetting('overlay_gradient', v);
						},
						help: 'Creates a gradient for better text readability',
					}),
					el(ToggleControl, {
						label: 'Use Liturgical Colors',
						checked: settings.use_liturgical_color === true,
						onChange: function (v) {
							updateSetting('use_liturgical_color', v);
						},
						help: 'Dynamic slides like Feast Day will use the liturgical color as overlay',
					})
				),

				// CTA Button Settings
				el(
					PanelBody,
					{ title: 'Call to Action Button', initialOpen: false },
					el(ColorPicker, {
						label: 'Button Color',
						value: settings.cta_color || '#d97706',
						onChange: function (v) {
							updateSetting('cta_color', v);
						},
					}),
					el(ColorPicker, {
						label: 'Button Hover Color',
						value: settings.cta_hover_color || '#b45309',
						onChange: function (v) {
							updateSetting('cta_hover_color', v);
						},
					}),
					el(
						'div',
						{
							style: {
								marginTop: '16px',
								padding: '12px',
								backgroundColor: '#f6f7f7',
								borderRadius: '4px',
							},
						},
						el('label', { style: { display: 'block', marginBottom: '8px', fontWeight: '500' } }, 'Preview'),
						el(
							'a',
							{
								href: '#',
								onClick: function (e) {
									e.preventDefault();
								},
								style: {
									display: 'inline-flex',
									alignItems: 'center',
									gap: '6px',
									padding: '10px 20px',
									backgroundColor: settings.cta_color || '#d97706',
									color: '#fff',
									textDecoration: 'none',
									borderRadius: '6px',
									fontSize: '14px',
									fontWeight: '600',
								},
							},
							'Learn More →'
						)
					)
				),

				// Rosary Images
				el(
					PanelBody,
					{ title: 'Rosary Mystery Images', initialOpen: false },
					el(RosaryImagesSettings, {
						rosaryImages: settings.rosary_images || {},
						onChange: function (v) {
							updateSetting('rosary_images', v);
						},
					})
				),

				// Season Images
				el(
					PanelBody,
					{ title: 'Liturgical Season Images', initialOpen: false },
					el(SeasonImagesSettings, {
						seasonImages: settings.season_images || {},
						onChange: function (v) {
							updateSetting('season_images', v);
						},
					})
				),

				// Shortcode Info
				el(
					PanelBody,
					{ title: 'Usage', initialOpen: false },
					el('p', null, 'Use this shortcode to display the slider:'),
					el(
						'code',
						{
							style: {
								display: 'block',
								padding: '12px',
								backgroundColor: '#23282d',
								color: '#fff',
								borderRadius: '4px',
								marginBottom: '12px',
							},
						},
						'[parish_slider]'
					),
					el('p', null, 'Or add the "Parish Slider" block in the block editor.'),
					el('h4', { style: { marginTop: '20px' } }, 'Available Dynamic Sources (' + sourceCount + ')'),
					el(
						'div',
						{ style: { maxHeight: '200px', overflowY: 'auto' } },
						Object.keys(dynamicSources).map(function (key) {
							var source = dynamicSources[key];
							return el(
								'div',
								{
									key: key,
									style: {
										padding: '8px',
										borderBottom: '1px solid #eee',
										display: 'flex',
										alignItems: 'center',
										gap: '8px',
									},
								},
								el(Dashicon, { icon: source.icon || 'admin-post' }),
								el(
									'div',
									null,
									el('strong', { style: { fontSize: '13px' } }, source.name),
									el('div', { style: { fontSize: '11px', color: '#666' } }, source.description)
								)
							);
						})
					)
				)
			),

			// Save Bar
			el(
				'div',
				{
					className: 'parish-save-bar',
					style: {
						marginTop: '20px',
						padding: '16px',
						backgroundColor: '#fff',
						borderTop: '1px solid #ddd',
						position: 'sticky',
						bottom: 0,
					},
				},
				el(Button, { isPrimary: true, isBusy: saving, onClick: save }, saving ? 'Saving...' : 'Save Slider Settings')
			),

			// Modals
			showAddModal &&
				el(SlideEditorModal, {
					slide: { type: 'manual', enabled: true },
					dynamicSources: dynamicSources,
					onSave: addSlide,
					onClose: function () {
						setShowAddModal(false);
					},
				}),
			editingSlide &&
				el(SlideEditorModal, {
					slide: editingSlide,
					dynamicSources: dynamicSources,
					onSave: updateSlide,
					onClose: function () {
						setEditingSlide(null);
					},
				})
		);
	}

	// Export to ParishCoreAdmin
	window.ParishCoreAdmin = window.ParishCoreAdmin || {};
	window.ParishCoreAdmin.SliderSettings = SliderSettings;
})(window);
