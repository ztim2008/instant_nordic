<?php

if (!function_exists('landingbuilder_get_runtime_block_titles')) {

	function landingbuilder_get_runtime_block_titles() {
		return [
			'core.navigation'       => 'Навигация',
			'core.hero'             => 'Первый экран (Hero)',
			'core.hero-heading'     => 'Главный экран с заголовком',
			'core.hero-actions'     => 'Главный экран с кнопками',
			'core.cards-grid'       => 'Сетка карточек',
			'core.feature-list'     => 'Список преимуществ',
			'ads.category-header'   => 'Шапка категории объявлений',
			'ads.filter-bar'        => 'Панель фильтров',
			'profile.cover-hero'    => 'Обложка профиля',
			'profile.quick-stats'   => 'Короткая статистика профиля',
			'pro.flex-composer'     => 'PRO: Гибкий компоновщик',
			'pro.metrics-grid-pro'  => 'PRO: Метрики и карточки',
			'pro.faq-adaptive-pro'  => 'PRO: FAQ адаптивный'
		];
	}

	function landingbuilder_runtime_normalize_ctype_name($value) {
		$name = trim((string) $value);
		if ($name === '') {
			return '';
		}

		return preg_match('/^[a-z0-9_{}\-]+$/i', $name) ? $name : '';
	}

	function landingbuilder_runtime_get_context_ctype_name() {
		try {
			$current_ctype = cmsModel::getCachedResult('current_ctype');
			if (is_array($current_ctype)) {
				return landingbuilder_runtime_normalize_ctype_name($current_ctype['name'] ?? '');
			}
		} catch (Throwable $exception) {
			// noop
		}

		return '';
	}

	function landingbuilder_runtime_resolve_ctype_name($preferred_ctype = '') {
		$preferred = landingbuilder_runtime_normalize_ctype_name($preferred_ctype);
		if ($preferred !== '') {
			return $preferred;
		}

		return landingbuilder_runtime_get_context_ctype_name();
	}

	function landingbuilder_runtime_resolve_content_sort($sort_key) {
		$map = [
			'date_desc'  => ['date_pub', 'desc'],
			'date_asc'   => ['date_pub', 'asc'],
			'id_desc'    => ['id', 'desc'],
			'id_asc'     => ['id', 'asc'],
			'title_asc'  => ['title', 'asc'],
			'title_desc' => ['title', 'desc']
		];

		$key = trim((string) $sort_key);
		return $map[$key] ?? $map['date_desc'];
	}

	function landingbuilder_runtime_fetch_ctype_items($ctype_name = '', $limit = 6, $sort_key = 'date_desc') {
		$resolved_ctype = landingbuilder_runtime_resolve_ctype_name($ctype_name);
		if ($resolved_ctype === '') {
			return [];
		}

		$safe_limit = (int) round((float) $limit);
		if ($safe_limit < 1) {
			$safe_limit = 1;
		}
		if ($safe_limit > 24) {
			$safe_limit = 24;
		}

		try {
			$content_model = cmsCore::getModel('content');
			$ctype = $content_model->getContentTypeByName($resolved_ctype);
			if (!$ctype) {
				return [];
			}

			list($order_by, $order_to) = landingbuilder_runtime_resolve_content_sort($sort_key);
			$content_model
				->orderBy($order_by, $order_to)
				->limit(0, $safe_limit);

			$items = $content_model->getContentItems($resolved_ctype);
			return is_array($items) ? array_values($items) : [];
		} catch (Throwable $exception) {
			return [];
		}
	}

	function landingbuilder_runtime_extract_item_value(array $item, $field_path, $fallback = '') {
		$path = trim((string) $field_path);
		if ($path === '') {
			return trim((string) $fallback);
		}

		$cursor = $item;
		foreach (explode('.', $path) as $chunk) {
			$key = trim((string) $chunk);
			if ($key === '' || !is_array($cursor) || !array_key_exists($key, $cursor)) {
				return trim((string) $fallback);
			}

			$cursor = $cursor[$key];
		}

		if (is_array($cursor)) {
			foreach (['title', 'name', 'label', 'value'] as $candidate) {
				if (array_key_exists($candidate, $cursor) && is_scalar($cursor[$candidate])) {
					return trim((string) $cursor[$candidate]);
				}
			}

			return trim((string) $fallback);
		}

		if (is_bool($cursor)) {
			return $cursor ? '1' : '0';
		}

		if (is_scalar($cursor)) {
			return trim((string) $cursor);
		}

		return trim((string) $fallback);
	}

	function landingbuilder_runtime_normalize_link_mode($value) {
		$mode = trim((string) $value);
		return in_array($mode, ['none', 'auto', 'field', 'template'], true) ? $mode : 'none';
	}

	function landingbuilder_runtime_sanitize_link($value) {
		$url = trim((string) $value);
		if ($url === '') {
			return '';
		}

		if (stripos($url, 'javascript:') === 0) {
			return '';
		}

		if (preg_match('~^(https?://|/|#|mailto:|tel:)~i', $url)) {
			return $url;
		}

		if (preg_match('~^[a-z0-9/_\-\.\?=&%#]+$~i', $url)) {
			return '/' . ltrim($url, '/');
		}

		return '';
	}

	function landingbuilder_runtime_build_template_link(array $item, $template) {
		$raw_template = trim((string) $template);
		if ($raw_template === '') {
			return '';
		}

		$result = preg_replace_callback('/\{([a-z0-9_.\-]+)\}/i', function ($matches) use ($item) {
			$token = trim((string) ($matches[1] ?? ''));
			if ($token === '') {
				return '';
			}

			if ($token === 'ctype') {
				$ctype = landingbuilder_runtime_extract_item_value($item, 'ctype_name', '');
				if ($ctype === '') {
					$ctype = landingbuilder_runtime_extract_item_value($item, 'ctype.name', '');
				}
				return $ctype;
			}

			return landingbuilder_runtime_extract_item_value($item, $token, '');
		}, $raw_template);

		return landingbuilder_runtime_sanitize_link($result);
	}

	function landingbuilder_runtime_resolve_item_link(array $item, $mode = 'none', $field_path = '', $template = '') {
		$normalized_mode = landingbuilder_runtime_normalize_link_mode($mode);
		if ($normalized_mode === 'none') {
			return '';
		}

		if ($normalized_mode === 'field') {
			$raw = landingbuilder_runtime_extract_item_value($item, $field_path, '');
			return landingbuilder_runtime_sanitize_link($raw);
		}

		if ($normalized_mode === 'template') {
			return landingbuilder_runtime_build_template_link($item, $template);
		}

		foreach (['url', 'href', 'link', 'item_url', 'seo_url'] as $candidate_field) {
			$raw = landingbuilder_runtime_extract_item_value($item, $candidate_field, '');
			$link = landingbuilder_runtime_sanitize_link($raw);
			if ($link !== '') {
				return $link;
			}
		}

		$ctype_name = landingbuilder_runtime_extract_item_value($item, 'ctype_name', '');
		if ($ctype_name === '') {
			$ctype_name = landingbuilder_runtime_extract_item_value($item, 'ctype.name', '');
		}
		$slug = landingbuilder_runtime_extract_item_value($item, 'slug', '');
		if ($ctype_name !== '' && $slug !== '') {
			return landingbuilder_runtime_sanitize_link('/' . trim($ctype_name, '/') . '/' . ltrim($slug, '/'));
		}

		$id = landingbuilder_runtime_extract_item_value($item, 'id', '');
		if ($ctype_name !== '' && $id !== '') {
			return landingbuilder_runtime_sanitize_link('/' . trim($ctype_name, '/') . '/' . $id);
		}

		return '';
	}

	function landingbuilder_runtime_is_visible($visibility, $device_type) {
		if (!is_array($visibility)) {
			return true;
		}

		if (!array_key_exists($device_type, $visibility)) {
			return true;
		}

		return $visibility[$device_type] !== false;
	}

	function landingbuilder_get_runtime_layout_class($layout) {
		$layout_class_map = [
			'1col'               => 'lb-grid-1',
			'2col_equal'         => 'lb-grid-2',
			'2col_sidebar_left'  => 'lb-grid-sidebar-left',
			'2col_sidebar_right' => 'lb-grid-sidebar-right',
			'3col_equal'         => 'lb-grid-3'
		];

		return $layout_class_map[$layout] ?? 'lb-grid-1';
	}

	function landingbuilder_runtime_normalize_device_key($device_type) {
		$key = mb_strtolower(trim((string) $device_type));
		if ($key === 'phone') {
			return 'mobile';
		}
		if ($key === 'pad') {
			return 'tablet';
		}
		return in_array($key, ['desktop', 'tablet', 'mobile'], true) ? $key : 'desktop';
	}

	function landingbuilder_runtime_parse_column_units($value) {
		if (!is_scalar($value)) {
			return null;
		}

		$units = (int) round((float) $value);
		if ($units < 1 || $units > 12) {
			return null;
		}

		return $units;
	}

	function landingbuilder_runtime_get_layout_default_units($layout, $columns_count) {
		$count = max(1, (int) $columns_count);
		if ($count <= 1) {
			return [12];
		}

		if ((string) $layout === '3col_equal') {
			return array_slice([4, 4, 4], 0, $count);
		}

		if ((string) $layout === '2col_sidebar_left') {
			return [4, 8];
		}

		if ((string) $layout === '2col_sidebar_right') {
			return [8, 4];
		}

		return array_slice([6, 6], 0, $count);
	}

	function landingbuilder_runtime_normalize_units(array $units, $total_units = 12) {
		$safe_total = max(1, (int) $total_units);
		$prepared = [];
		foreach ($units as $value) {
			$number = (float) $value;
			$prepared[] = $number > 0 ? $number : 1.0;
		}

		if (!$prepared) {
			return [$safe_total];
		}

		$sum = array_sum($prepared);
		if ($sum <= 0) {
			$sum = 1;
		}

		$scaled = [];
		foreach ($prepared as $value) {
			$scaled[] = ($value / $sum) * $safe_total;
		}

		$integers = [];
		foreach ($scaled as $value) {
			$integers[] = max(1, (int) floor($value));
		}

		$remainder = $safe_total - array_sum($integers);
		if ($remainder > 0) {
			$fractions = [];
			foreach ($scaled as $index => $value) {
				$fractions[] = ['index' => $index, 'fraction' => $value - floor($value)];
			}
			usort($fractions, function ($left, $right) {
				if ($left['fraction'] === $right['fraction']) {
					return 0;
				}
				return ($left['fraction'] < $right['fraction']) ? 1 : -1;
			});

			$slots_count = count($fractions);
			$guard = 0;
			while ($remainder > 0 && $slots_count > 0 && $guard < 256) {
				$slot = $fractions[$guard % $slots_count];
				$integers[$slot['index']] += 1;
				$remainder -= 1;
				$guard += 1;
			}
		}

		return $integers;
	}

	function landingbuilder_runtime_width_inherit_enabled(array $section) {
		$settings = !empty($section['settings']) && is_array($section['settings']) ? $section['settings'] : [];
		return ($settings['width_inherit'] ?? true) !== false;
	}

	function landingbuilder_runtime_is_stacked_for_device(array $section, $device_key) {
		$settings = !empty($section['settings']) && is_array($section['settings']) ? $section['settings'] : [];
		$device = landingbuilder_runtime_normalize_device_key($device_key);

		if ($device === 'mobile') {
			return ($settings['stack_phone'] ?? true) !== false;
		}

		if ($device === 'tablet') {
			return ($settings['stack_tablet'] ?? false) === true;
		}

		return false;
	}

	function landingbuilder_runtime_get_width_preference_keys($device_key, $inherit_enabled) {
		$device = landingbuilder_runtime_normalize_device_key($device_key);
		$keys = [];

		if ($device === 'mobile') {
			$keys = ['mobile', 'phone'];
			if ($inherit_enabled) {
				$keys = array_merge($keys, ['tablet', 'pad', 'desktop']);
			}
		} elseif ($device === 'tablet') {
			$keys = ['tablet', 'pad'];
			if ($inherit_enabled) {
				$keys[] = 'desktop';
			}
		} else {
			$keys = ['desktop'];
		}

		$normalized = [];
		foreach ($keys as $key) {
			$candidate = mb_strtolower(trim((string) $key));
			if ($candidate === '' || in_array($candidate, $normalized, true)) {
				continue;
			}
			$normalized[] = $candidate;
		}

		return $normalized;
	}

	function landingbuilder_runtime_resolve_section_units(array $section, $device_key) {
		$columns = !empty($section['columns']) && is_array($section['columns']) ? array_values($section['columns']) : [];
		if (!$columns) {
			return [12];
		}

		if (landingbuilder_runtime_is_stacked_for_device($section, $device_key)) {
			return [12];
		}

		$defaults = landingbuilder_runtime_normalize_units(
			landingbuilder_runtime_get_layout_default_units($section['layout'] ?? '1col', count($columns)),
			12
		);
		$preference_keys = landingbuilder_runtime_get_width_preference_keys(
			$device_key,
			landingbuilder_runtime_width_inherit_enabled($section)
		);

		$raw_units = [];
		foreach ($columns as $column_index => $column) {
			$width_map = !empty($column['width']) && is_array($column['width']) ? $column['width'] : [];
			$resolved = null;

			foreach ($preference_keys as $preference_key) {
				$resolved = landingbuilder_runtime_parse_column_units($width_map[$preference_key] ?? null);
				if ($resolved !== null) {
					break;
				}
			}

			if ($resolved === null) {
				$resolved = $defaults[$column_index] ?? 1;
			}

			$raw_units[] = $resolved;
		}

		return landingbuilder_runtime_normalize_units($raw_units, 12);
	}

	function landingbuilder_runtime_units_to_grid_template(array $units) {
		$prepared = [];
		foreach ($units as $unit) {
			$size = max(1, (int) $unit);
			$prepared[] = 'minmax(0,' . $size . 'fr)';
		}

		if (!$prepared) {
			return 'minmax(0,1fr)';
		}

		return implode(' ', $prepared);
	}

	function landingbuilder_runtime_get_section_grid_templates(array $section) {
		return [
			'desktop' => landingbuilder_runtime_units_to_grid_template(landingbuilder_runtime_resolve_section_units($section, 'desktop')),
			'tablet'  => landingbuilder_runtime_units_to_grid_template(landingbuilder_runtime_resolve_section_units($section, 'tablet')),
			'mobile'  => landingbuilder_runtime_units_to_grid_template(landingbuilder_runtime_resolve_section_units($section, 'mobile'))
		];
	}

	function landingbuilder_runtime_base_autoscale_enabled(array $context = []) {
		$section_enabled = !empty($context['section_autoscale_base_blocks']);
		$column_units = isset($context['column_units']) ? (int) $context['column_units'] : 0;
		$slot_key = (string) ($context['slot_key'] ?? '');

		if (in_array($slot_key, ['content_sidebar_left', 'content_sidebar_right'], true)) {
			return false;
		}

		return $section_enabled && $column_units === 12;
	}

	function landingbuilder_runtime_count_visible_columns(array $section, $device_type) {
		$columns = !empty($section['columns']) && is_array($section['columns']) ? $section['columns'] : [];
		$count = 0;

		foreach ($columns as $column) {
			if (!landingbuilder_runtime_is_visible($column['visibility'] ?? [], $device_type)) {
				continue;
			}

			$count++;
		}

		return $count;
	}

	function landingbuilder_get_runtime_overlay_column_class($layout, $column_index, $columns_count) {
		if ($layout === '2col_sidebar_right') {
			return $column_index === 0 ? 'col-lg-8 col-md-7' : 'col-lg-4 col-md-5';
		}

		if ($layout === '2col_sidebar_left') {
			return $column_index === 0 ? 'col-lg-4 col-md-5' : 'col-lg-8 col-md-7';
		}

		if ($layout === '3col_equal') {
			return 'col-lg-4 col-md-6';
		}

		if ($layout === '2col_equal') {
			return 'col-md-6';
		}

		if ($columns_count > 1) {
			return 'col-md-6';
		}

		return 'col-12';
	}

	function landingbuilder_render_runtime_block(array $node, array $context = []) {
		$surface = $context['surface'] ?? 'runtime';
		$block_titles = landingbuilder_get_runtime_block_titles();
		$key = $node['source_key'] ?: $node['label'];
		$title = $block_titles[$key] ?? (($node['block_meta']['title'] ?? '') ?: ($node['label'] ?: 'Блок'));
		$notes = trim((string) ($node['notes'] ?? ''));
		$source_key = trim((string) ($node['source_key'] ?? ''));
		$options = isset($node['options']) && is_array($node['options']) ? $node['options'] : [];
		$meta = isset($node['block_meta']) && is_array($node['block_meta']) ? $node['block_meta'] : [];
		$eyebrow = trim((string) ($options['eyebrow'] ?? ''));
		$heading = trim((string) ($options['title'] ?? $title));
		$text = trim((string) ($options['text'] ?? ($meta['description'] ?? '')));
		$items_text = trim((string) ($options['items_text'] ?? ''));
		$items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $items_text))));
		$primary_label = trim((string) ($options['primary_label'] ?? ''));
		$secondary_label = trim((string) ($options['secondary_label'] ?? ''));
		$button_label = trim((string) ($options['button_label'] ?? $primary_label ?? ''));
		$button_url_raw = trim((string) ($options['button_url'] ?? ''));
		$button_url = preg_match('~^(https?://|/|#)~i', $button_url_raw) ? $button_url_raw : '';
		$image_url_raw = trim((string) ($options['image_url'] ?? ''));
		$image_url = preg_match('~^(https?://|/)~i', $image_url_raw) ? $image_url_raw : '';
		$html = (string) ($options['html'] ?? '');
		$normalize_color = static function ($value, $fallback) {
			$color = trim((string) $value);
			return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $color) ? $color : $fallback;
		};
		$normalize_number = static function ($value, $fallback, $min, $max) {
			if (!is_numeric($value)) {
				return $fallback;
			}

			$number = (int) round((float) $value);
			if ($number < $min) {
				$number = $min;
			}
			if ($number > $max) {
				$number = $max;
			}

			return $number;
		};
		$normalize_enum = static function ($value, array $allowed, $fallback) {
			$value = trim((string) $value);
			return in_array($value, $allowed, true) ? $value : $fallback;
		};

		$body = '';
		if ($key === 'core.hero') {
			$action = '';
			if ($button_label !== '') {
				if ($button_url !== '') {
					$action = '<a class="btn btn-primary nordic-lb-hero__btn" href="' . html($button_url, false) . '">' . html($button_label, false) . '</a>';
				} else {
					$action = '<span class="btn btn-primary disabled nordic-lb-hero__btn" aria-disabled="true">' . html($button_label, false) . '</span>';
				}
			}

			$media = $image_url !== ''
				? '<div class="nordic-lb-hero__media"><img src="' . html($image_url, false) . '" alt="" loading="lazy"></div>'
				: '';

			$body = ''
				. '<div class="nordic-lb-hero">'
					. '<div class="nordic-lb-hero__inner">'
						. '<div class="nordic-lb-hero__content">'
							. ($eyebrow ? '<div class="nordic-lb-hero__eyebrow">' . html($eyebrow, false) . '</div>' : '')
							. '<h1 class="nordic-lb-hero__title">' . html($heading ?: 'Первый экран страницы', false) . '</h1>'
							. ($text ? '<p class="nordic-lb-hero__text">' . html($text, false) . '</p>' : '')
							. ($action ? '<div class="nordic-lb-hero__actions">' . $action . '</div>' : '')
						. '</div>'
						. $media
					. '</div>'
				. '</div>';
		} elseif ($key === 'core.hero-heading') {
			$body = ($eyebrow ? '<div class="lb-block-eyebrow">' . html($eyebrow, false) . '</div>' : '')
				. '<h2>' . html($heading ?: 'Главный экран страницы', false) . '</h2>'
				. '<p>' . html($text ?: 'Крупный вводный блок для основного обещания, подзаголовка и первого впечатления.', false) . '</p>';
		} elseif ($key === 'core.text') {
			$body = '<h3>' . html($heading ?: 'Текст', false) . '</h3>'
				. ($text ? '<p>' . nl2br(html($text, false)) . '</p>' : '<p class="text-muted small">Добавьте текст в настройках блока.</p>');
		} elseif ($key === 'core.raw-html') {
			$body = $html !== '' ? $html : '<div class="text-muted small">Добавьте HTML в настройках блока.</div>';
		} elseif ($key === 'core.navigation') {
			$menu_name = trim((string) ($options['menu'] ?? ''));
			$detect_active = !empty($options['is_detect']);
			$strict_active = !empty($options['is_detect_strict']);
			$allow_multiple_active = !$strict_active;
			$max_items = (int) ($options['max_items'] ?? 0);
			$menu_template = trim((string) ($options['template'] ?? 'menu'));
			$css_class = trim((string) ($options['class'] ?? ''));
			$navbar_color = trim((string) ($options['navbar_color_scheme'] ?? ''));
			$nav_style = trim((string) ($options['menu_nav_style'] ?? ''));
			$nav_style_add = trim((string) ($options['menu_nav_style_add'] ?? ''));

			$classes = array_values(array_filter(array_map('trim', preg_split('/\s+/', $css_class ?: 'menu nav'))));
			foreach ([$navbar_color, $nav_style, $nav_style_add] as $extra_class) {
				if ($extra_class !== '') {
					$classes[] = $extra_class;
				}
			}
			$classes = array_values(array_unique(array_filter($classes)));
			$css_class = implode(' ', $classes);

			if ($menu_name === '') {
				$body = $surface === 'runtime'
					? '<div class="text-muted small">Выберите меню в настройках блока «Навигация».</div>'
					: '<p>' . html('Выберите меню в настройках блока «Навигация».', false) . '</p>';
			} else {
				$template = cmsTemplate::getInstance();

				if (!$template->hasMenu($menu_name)) {
					$menu_items = modelMenu::getMenuItemsByName($menu_name);
					if ($menu_items) {
						$template->setMenuItems($menu_name, $menu_items);
					}
				}

				ob_start();
				$template->menu(
					$menu_name,
					$detect_active,
					$css_class,
					$max_items,
					$allow_multiple_active,
					$menu_template,
					''
				);
				$menu_html = trim((string) ob_get_clean());

				$body = $menu_html !== '' ? $menu_html : '<div class="text-muted small">Меню пустое или не найдено.</div>';
			}

			// Навигация — системный блок, в runtime выводим без "карточки".
			if ($surface === 'runtime') {
				return $body;
			}
		} elseif ($key === 'core.hero-actions') {
			$actions = '';
			if ($primary_label || $secondary_label) {
				$actions = '<div class="lb-block-actions">';
				if ($primary_label) {
					$actions .= '<span class="lb-block-action lb-block-action--primary">' . html($primary_label, false) . '</span>';
				}
				if ($secondary_label) {
					$actions .= '<span class="lb-block-action lb-block-action--secondary">' . html($secondary_label, false) . '</span>';
				}
				$actions .= '</div>';
			}

			$body = '<h2>' . html($heading ?: 'Главное действие', false) . '</h2>'
				. '<p>' . html($text ?: 'Зона для призыва к действию, кнопок и короткой поясняющей строки.', false) . '</p>'
				. $actions;
		} elseif ($key === 'core.cards-grid') {
			if (!$items) {
				$items = ['Первая карточка', 'Вторая карточка', 'Третья карточка'];
			}

			$body = '<h3>' . html($heading ?: 'Карточки', false) . '</h3>'
				. ($text ? '<p>' . html($text, false) . '</p>' : '')
				. '<div class="lb-block-grid">' . implode('', array_map(function ($item) {
					return '<div class="lb-block-grid__item">' . html($item, false) . '</div>';
				}, $items)) . '</div>';
		} elseif ($key === 'core.feature-list') {
			if (!$items) {
				$items = ['Короткие тезисы', 'Простая визуальная подача', 'Подходит для доверительных аргументов'];
			}

			$body = '<h3>' . html($heading ?: 'Преимущества', false) . '</h3><ul>' . implode('', array_map(function ($item) {
				return '<li>' . html($item, false) . '</li>';
			}, $items)) . '</ul>';
		} elseif ($key === 'ads.category-header') {
			$body = ($eyebrow ? '<div class="lb-block-eyebrow">' . html($eyebrow, false) . '</div>' : '')
				. '<h2>' . html($heading ?: ($surface === 'overlay' ? 'Шапка категории' : 'Категория объявлений'), false) . '</h2>'
				. '<p>' . html($text ?: ($surface === 'overlay'
					? 'Эта секция уже встраивается в живую страницу и может усиливать контекст категории перед списком объявлений.'
					: 'Шапка категории с контекстом страницы, подводкой и визуальным акцентом.'), false) . '</p>';
		} elseif ($key === 'ads.filter-bar') {
			if (!$items) {
				$items = ['Новые', 'С доставкой', 'Проверенные продавцы'];
			}

			$body = '<h3>' . html($heading ?: ($surface === 'overlay' ? 'Панель отбора' : 'Фильтры категории'), false) . '</h3>'
				. ($text ? '<p>' . html($text, false) . '</p>' : '')
				. '<div class="lb-block-chip-list">' . implode('', array_map(function ($item) {
					return '<span class="lb-block-chip">' . html($item, false) . '</span>';
				}, $items)) . '</div>';
		} elseif ($key === 'profile.cover-hero') {
			$body = ($eyebrow ? '<div class="lb-block-eyebrow">' . html($eyebrow, false) . '</div>' : '')
				. '<h2>' . html($heading ?: 'Обложка профиля', false) . '</h2>'
				. '<p>' . html($text ?: 'Крупный верхний блок для имени, описания и визуального образа профиля.', false) . '</p>';
		} elseif ($key === 'profile.quick-stats') {
			if (!$items) {
				$items = ['120|завершенных заказов', '4.9|средний рейтинг', '7 лет|на рынке'];
			}

			$body = '<h3>' . html($heading ?: 'Статистика профиля', false) . '</h3>'
				. '<div class="lb-block-stat-grid">' . implode('', array_map(function ($item) {
					$parts = array_map('trim', explode('|', $item, 2));
					$value = $parts[0] ?? '';
					$caption = $parts[1] ?? '';
					return '<div class="lb-block-stat"><div class="lb-block-stat__value">' . html($value, false) . '</div><div class="lb-block-stat__caption">' . html($caption ?: 'Показатель', false) . '</div></div>';
				}, $items)) . '</div>';
		} elseif ($key === 'pro.flex-composer') {
			$layout_mode = $normalize_enum($options['layout_mode'] ?? '', ['split-left', 'split-right', 'stack-center', 'media-background'], 'split-left');
			$columns_ratio = $normalize_enum($options['columns_ratio'] ?? '', ['6-6', '5-7', '7-5', '4-8', '8-4'], '6-6');
			$content_align = $normalize_enum($options['content_align'] ?? '', ['left', 'center', 'right'], 'left');
			$content_width = $normalize_enum($options['content_width'] ?? '', ['narrow', 'standard', 'wide', 'full'], 'standard');
			$image_fit = $normalize_enum($options['image_fit'] ?? '', ['cover', 'contain'], 'cover');
			$image_shape = $normalize_enum($options['image_shape'] ?? '', ['rounded', 'square', 'circle'], 'rounded');
			$image_shadow = $normalize_enum($options['image_shadow'] ?? '', ['none', 'soft', 'strong'], 'soft');
			$background_mode = $normalize_enum($options['background_mode'] ?? '', ['solid', 'gradient', 'none'], 'gradient');
			$surface_mode = $normalize_enum($options['surface_mode'] ?? '', ['transparent', 'card', 'glass'], 'glass');
			$padding_y = $normalize_number($options['padding_y'] ?? 56, 56, 20, 180);
			$gap = $normalize_number($options['gap'] ?? 28, 28, 8, 96);
			$radius = $normalize_number($options['radius'] ?? 22, 22, 0, 60);

			$bg_start = $normalize_color($options['bg_color_start'] ?? '#0f172a', '#0f172a');
			$bg_end = $normalize_color($options['bg_color_end'] ?? '#1d4ed8', '#1d4ed8');
			$text_color = $normalize_color($options['text_color'] ?? '#f8fafc', '#f8fafc');
			$muted_text_color = $normalize_color($options['muted_text_color'] ?? '#cbd5e1', '#cbd5e1');
			$accent_color = $normalize_color($options['accent_color'] ?? '#22c55e', '#22c55e');
			$surface_color = $normalize_color($options['surface_color'] ?? '#0b1220', '#0b1220');

			$secondary_label_local = trim((string) ($options['secondary_label'] ?? $secondary_label));
			$secondary_url_raw = trim((string) ($options['secondary_url'] ?? ''));
			$secondary_url = preg_match('~^(https?://|/|#)~i', $secondary_url_raw) ? $secondary_url_raw : '';

			$features_text = trim((string) ($options['features_text'] ?? ''));
			$features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $features_text))));
			$show_media = !empty($options['show_media']) && $image_url !== '';

			$ratio_map = [
				'6-6' => 'minmax(0,6fr) minmax(0,6fr)',
				'5-7' => 'minmax(0,5fr) minmax(0,7fr)',
				'7-5' => 'minmax(0,7fr) minmax(0,5fr)',
				'4-8' => 'minmax(0,4fr) minmax(0,8fr)',
				'8-4' => 'minmax(0,8fr) minmax(0,4fr)'
			];
			$content_width_map = [
				'narrow'   => '640px',
				'standard' => '820px',
				'wide'     => '1080px',
				'full'     => '100%'
			];
			$text_align_map = [
				'left' => 'left',
				'center' => 'center',
				'right' => 'right'
			];
			$items_align_map = [
				'left' => 'flex-start',
				'center' => 'center',
				'right' => 'flex-end'
			];

			$section_styles = ['padding:' . $padding_y . 'px 28px', 'border-radius:' . $radius . 'px', 'color:' . $text_color, 'overflow:hidden'];
			if ($layout_mode === 'media-background' && $show_media) {
				$bg_image_safe = preg_replace('~[^a-z0-9:/._?=&%\-]~i', '', $image_url);
				$section_styles[] = "background-image:linear-gradient(135deg, {$bg_start}DD 0%, {$bg_end}CC 100%),url('{$bg_image_safe}')";
				$section_styles[] = 'background-size:cover';
				$section_styles[] = 'background-position:center';
			} elseif ($background_mode === 'gradient') {
				$section_styles[] = 'background:linear-gradient(135deg,' . $bg_start . ' 0%,' . $bg_end . ' 100%)';
			} elseif ($background_mode === 'solid') {
				$section_styles[] = 'background:' . $bg_start;
			} else {
				$section_styles[] = 'background:transparent';
			}

			$surface_styles = ['border-radius:' . max(10, $radius - 4) . 'px', 'padding:24px'];
			if ($surface_mode === 'card') {
				$surface_styles[] = 'background:' . $surface_color;
				$surface_styles[] = 'border:1px solid ' . $accent_color;
				$surface_styles[] = 'box-shadow:0 14px 34px rgba(15,23,42,0.22)';
			} elseif ($surface_mode === 'glass') {
				$surface_styles[] = 'background:rgba(255,255,255,0.10)';
				$surface_styles[] = 'border:1px solid rgba(255,255,255,0.26)';
				$surface_styles[] = 'backdrop-filter:blur(6px)';
			} else {
				$surface_styles[] = 'background:transparent';
			}

			$button_primary = '';
			if ($button_label !== '') {
				$button_primary = $button_url !== ''
					? '<a href="' . html($button_url, false) . '" class="btn" style="background:' . html($accent_color, false) . ';color:#ffffff;border:none;padding:10px 18px;border-radius:999px;font-weight:700;">' . html($button_label, false) . '</a>'
					: '<span class="btn" style="background:' . html($accent_color, false) . ';color:#ffffff;border:none;padding:10px 18px;border-radius:999px;font-weight:700;opacity:.7;">' . html($button_label, false) . '</span>';
			}

			$button_secondary = '';
			if ($secondary_label_local !== '') {
				$button_secondary = $secondary_url !== ''
					? '<a href="' . html($secondary_url, false) . '" class="btn" style="background:transparent;color:' . html($text_color, false) . ';border:1px solid ' . html($accent_color, false) . ';padding:10px 18px;border-radius:999px;font-weight:600;">' . html($secondary_label_local, false) . '</a>'
					: '<span class="btn" style="background:transparent;color:' . html($text_color, false) . ';border:1px solid ' . html($accent_color, false) . ';padding:10px 18px;border-radius:999px;font-weight:600;opacity:.8;">' . html($secondary_label_local, false) . '</span>';
			}

			$actions_html = ($button_primary || $button_secondary)
				? '<div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:' . html($items_align_map[$content_align], false) . ';margin-top:18px;">' . $button_primary . $button_secondary . '</div>'
				: '';

			$features_html = $features
				? '<div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:' . html($items_align_map[$content_align], false) . ';margin-top:16px;">' . implode('', array_map(function ($item) use ($accent_color) {
					return '<span style="display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,0.12);border:1px solid ' . html($accent_color, false) . ';font-size:12px;">' . html($item, false) . '</span>';
				}, $features)) . '</div>'
				: '';

			$image_radius = $image_shape === 'circle' ? '999px' : ($image_shape === 'square' ? '0px' : (string) max(8, $radius - 6) . 'px');
			$image_shadow_css = $image_shadow === 'strong'
				? '0 28px 52px rgba(2,6,23,0.48)'
				: ($image_shadow === 'soft' ? '0 16px 30px rgba(2,6,23,0.28)' : 'none');
			$media_html = $show_media && $layout_mode !== 'media-background'
				? '<div style="width:100%;min-height:220px;"><img src="' . html($image_url, false) . '" alt="" loading="lazy" style="display:block;width:100%;height:100%;min-height:220px;object-fit:' . html($image_fit, false) . ';border-radius:' . html($image_radius, false) . ';box-shadow:' . html($image_shadow_css, false) . ';" /></div>'
				: '';

			$content_html = ''
				. ($eyebrow ? '<div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:' . html($muted_text_color, false) . ';margin-bottom:10px;">' . html($eyebrow, false) . '</div>' : '')
				. '<h2 style="margin:0 0 12px;font-size:clamp(28px,4vw,46px);line-height:1.1;color:' . html($text_color, false) . ';">' . html($heading ?: 'Гибкий блок', false) . '</h2>'
				. ($text ? '<p style="margin:0;color:' . html($muted_text_color, false) . ';font-size:16px;line-height:1.6;">' . html($text, false) . '</p>' : '')
				. $actions_html
				. $features_html;

			$content_wrapper = '<div style="max-width:' . html($content_width_map[$content_width], false) . ';text-align:' . html($text_align_map[$content_align], false) . ';margin:' . ($content_align === 'center' ? '0 auto' : ($content_align === 'right' ? '0 0 0 auto' : '0')) . ';">' . $content_html . '</div>';
			$content_box = '<div style="' . html(implode(';', $surface_styles), false) . '">' . $content_wrapper . '</div>';

			if ($layout_mode === 'stack-center' || $layout_mode === 'media-background') {
				$body = '<section style="' . html(implode(';', $section_styles), false) . '"><div style="max-width:1200px;margin:0 auto;display:grid;gap:' . $gap . 'px;">' . $content_box . $media_html . '</div></section>';
			} else {
				$grid_template = $show_media ? ($ratio_map[$columns_ratio] ?? $ratio_map['6-6']) : 'minmax(0,1fr)';
				$first = $content_box;
				$second = $media_html;
				if ($layout_mode === 'split-right') {
					$first = $media_html;
					$second = $content_box;
				}

				$body = '<section style="' . html(implode(';', $section_styles), false) . '"><div style="max-width:1280px;margin:0 auto;display:grid;grid-template-columns:' . html($grid_template, false) . ';gap:' . $gap . 'px;align-items:center;">' . $first . $second . '</div></section>';
			}
		} elseif ($key === 'pro.metrics-grid-pro') {
			$columns = $normalize_enum($options['columns'] ?? '', ['2', '3', '4'], '3');
			$card_style = $normalize_enum($options['card_style'] ?? '', ['soft', 'outline', 'solid', 'glass'], 'soft');
			$data_source_mode = $normalize_enum($options['data_source_mode'] ?? '', ['manual', 'ctype.list'], 'manual');
			$data_link_mode = landingbuilder_runtime_normalize_link_mode($options['data_link_mode'] ?? 'none');
			$data_link_field = trim((string) ($options['data_link_field'] ?? 'url'));
			$data_link_template = trim((string) ($options['data_link_template'] ?? '/{ctype}/{slug}'));
			$section_bg = $normalize_color($options['section_bg'] ?? '#f8fafc', '#f8fafc');
			$card_bg = $normalize_color($options['card_bg'] ?? '#ffffff', '#ffffff');
			$value_color = $normalize_color($options['value_color'] ?? '#0f172a', '#0f172a');
			$label_color = $normalize_color($options['label_color'] ?? '#334155', '#334155');
			$note_color = $normalize_color($options['note_color'] ?? '#64748b', '#64748b');
			$accent_color = $normalize_color($options['accent_color'] ?? '#2563eb', '#2563eb');
			$border_color = $normalize_color($options['border_color'] ?? '#dbeafe', '#dbeafe');
			$radius = $normalize_number($options['radius'] ?? 16, 16, 0, 44);
			$padding_y = $normalize_number($options['padding_y'] ?? 44, 44, 16, 140);
			$gap = $normalize_number($options['gap'] ?? 18, 18, 8, 56);

			$items_rows = [];
			if ($data_source_mode === 'ctype.list') {
				$data_items = landingbuilder_runtime_fetch_ctype_items(
					$options['data_ctype_name'] ?? '',
					$options['data_limit'] ?? 6,
					$options['data_sort'] ?? 'date_desc'
				);

				$value_field = trim((string) ($options['data_value_field'] ?? 'id'));
				$label_field = trim((string) ($options['data_label_field'] ?? 'title'));
				$note_field = trim((string) ($options['data_note_field'] ?? 'date_pub'));

				foreach ($data_items as $item) {
					if (!is_array($item)) {
						continue;
					}

					$value = landingbuilder_runtime_extract_item_value($item, $value_field, (string) ($item['id'] ?? ''));
					$label = landingbuilder_runtime_extract_item_value($item, $label_field, (string) ($item['title'] ?? $value));
					$note = landingbuilder_runtime_extract_item_value($item, $note_field, (string) ($item['date_pub'] ?? ''));
					$item_link = landingbuilder_runtime_resolve_item_link($item, $data_link_mode, $data_link_field, $data_link_template);

					if ($value === '' && $label === '' && $note === '') {
						continue;
					}

					$value = str_replace('|', '/', $value);
					$label = str_replace('|', '/', $label);
					$note = str_replace('|', '/', $note);
					$item_link = str_replace('|', '', $item_link);
					$items_rows[] = $value . '|' . $label . '|' . $note . '|' . $item_link;
				}
			}

			if (!$items_rows) {
				$items_rows = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($options['items_text'] ?? $items_text)))));
			}
			if (!$items_rows) {
				$items_rows = ['1200|Лидов в месяц|Среднее за квартал', '4.9|Рейтинг|На основании 840 отзывов', '18 мин|Ответ менеджера|Средний SLA'];
			}

			$grid_map = ['2' => 'repeat(2,minmax(0,1fr))', '3' => 'repeat(3,minmax(0,1fr))', '4' => 'repeat(4,minmax(0,1fr))'];
			$card_base = ['border-radius:' . $radius . 'px', 'padding:18px'];
			if ($card_style === 'solid') {
				$card_base[] = 'background:' . $accent_color;
				$card_base[] = 'border:1px solid ' . $accent_color;
			} elseif ($card_style === 'outline') {
				$card_base[] = 'background:transparent';
				$card_base[] = 'border:1px solid ' . $border_color;
			} elseif ($card_style === 'glass') {
				$card_base[] = 'background:rgba(255,255,255,0.58)';
				$card_base[] = 'border:1px solid ' . $border_color;
				$card_base[] = 'backdrop-filter:blur(4px)';
			} else {
				$card_base[] = 'background:' . $card_bg;
				$card_base[] = 'border:1px solid ' . $border_color;
			}

			$cards_html = implode('', array_map(function ($row) use ($card_base, $value_color, $label_color, $note_color) {
				$parts = array_map('trim', explode('|', $row, 4));
				$value = $parts[0] ?? '';
				$label = $parts[1] ?? '';
				$note = $parts[2] ?? '';
				$link = landingbuilder_runtime_sanitize_link($parts[3] ?? '');
				$content = ''
					. '<div style="font-size:clamp(28px,3.5vw,42px);font-weight:800;line-height:1;color:' . html($value_color, false) . ';">' . html($value ?: '0', false) . '</div>'
					. '<div style="margin-top:6px;font-size:14px;font-weight:700;color:' . html($label_color, false) . ';">' . html($label ?: 'Показатель', false) . '</div>'
					. ($note ? '<div style="margin-top:4px;font-size:12px;color:' . html($note_color, false) . ';">' . html($note, false) . '</div>' : '');

				if ($link !== '') {
					$content = '<a href="' . html($link, false) . '" style="display:block;color:inherit;text-decoration:none;">' . $content . '</a>';
				}

				return '<div style="' . html(implode(';', $card_base), false) . '">'
					. $content
					. '</div>';
			}, $items_rows));

			$body = ''
				. '<section style="padding:' . $padding_y . 'px 28px;background:' . html($section_bg, false) . ';border-radius:' . $radius . 'px;">'
				. '<div style="max-width:1220px;margin:0 auto;">'
				. '<h3 style="margin:0 0 10px;font-size:clamp(24px,3vw,36px);color:' . html($value_color, false) . ';">' . html($heading ?: 'Результаты в цифрах', false) . '</h3>'
				. ($text ? '<p style="margin:0 0 18px;color:' . html($label_color, false) . ';font-size:15px;line-height:1.6;">' . html($text, false) . '</p>' : '')
				. '<div style="display:grid;grid-template-columns:' . html($grid_map[$columns], false) . ';gap:' . $gap . 'px;">' . $cards_html . '</div>'
				. '</div>'
				. '</section>';
		} elseif ($key === 'pro.faq-adaptive-pro') {
			$layout_mode = $normalize_enum($options['layout_mode'] ?? '', ['single', 'two'], 'single');
			$open_first = !empty($options['open_first']);
			$data_source_mode = $normalize_enum($options['data_source_mode'] ?? '', ['manual', 'ctype.list'], 'manual');
			$data_link_mode = landingbuilder_runtime_normalize_link_mode($options['data_link_mode'] ?? 'none');
			$data_link_field = trim((string) ($options['data_link_field'] ?? 'url'));
			$data_link_template = trim((string) ($options['data_link_template'] ?? '/{ctype}/{slug}'));
			$section_bg = $normalize_color($options['section_bg'] ?? '#ffffff', '#ffffff');
			$question_bg = $normalize_color($options['question_bg'] ?? '#f8fafc', '#f8fafc');
			$question_color = $normalize_color($options['question_color'] ?? '#0f172a', '#0f172a');
			$answer_color = $normalize_color($options['answer_color'] ?? '#334155', '#334155');
			$border_color = $normalize_color($options['border_color'] ?? '#e2e8f0', '#e2e8f0');
			$accent_color = $normalize_color($options['accent_color'] ?? '#2563eb', '#2563eb');
			$radius = $normalize_number($options['radius'] ?? 14, 14, 0, 36);
			$padding_y = $normalize_number($options['padding_y'] ?? 40, 40, 16, 120);
			$gap = $normalize_number($options['gap'] ?? 12, 12, 6, 40);

			$faq_rows = [];
			if ($data_source_mode === 'ctype.list') {
				$data_items = landingbuilder_runtime_fetch_ctype_items(
					$options['data_ctype_name'] ?? '',
					$options['data_limit'] ?? 6,
					$options['data_sort'] ?? 'date_desc'
				);
				$question_field = trim((string) ($options['data_question_field'] ?? 'title'));
				$answer_field = trim((string) ($options['data_answer_field'] ?? 'teaser'));

				foreach ($data_items as $item) {
					if (!is_array($item)) {
						continue;
					}

					$question = landingbuilder_runtime_extract_item_value($item, $question_field, (string) ($item['title'] ?? ''));
					$answer = landingbuilder_runtime_extract_item_value($item, $answer_field, '');
					$item_link = landingbuilder_runtime_resolve_item_link($item, $data_link_mode, $data_link_field, $data_link_template);

					if ($answer === '') {
						foreach (['teaser', 'description', 'content', 'seo_desc', 'seo_description'] as $fallback_field) {
							$answer = landingbuilder_runtime_extract_item_value($item, $fallback_field, '');
							if ($answer !== '') {
								break;
							}
						}
					}

					$question = trim(strip_tags($question));
					$answer = trim(strip_tags($answer));
					if (mb_strlen($answer) > 320) {
						$answer = mb_substr($answer, 0, 317) . '...';
					}

					if ($question === '' && $answer === '') {
						continue;
					}

					$question = str_replace('|', '/', $question);
					$answer = str_replace('|', '/', $answer);
					$item_link = str_replace('|', '', $item_link);
					$faq_rows[] = $question . '|' . $answer . '|' . $item_link;
				}
			}

			if (!$faq_rows) {
				$faq_rows = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($options['items_text'] ?? $items_text)))));
			}
			if (!$faq_rows) {
				$faq_rows = [
					'Сколько длится запуск?|Обычно 5-10 рабочих дней.',
					'Есть ли поддержка?|Да, сопровождение включено.',
					'Можно ли интегрировать CRM?|Да, подключаем любую популярную CRM.'
				];
			}

			$faq_items = [];
			foreach ($faq_rows as $index => $row) {
				$parts = array_map('trim', explode('|', $row, 3));
				$question = $parts[0] ?? '';
				$answer = $parts[1] ?? '';
				$link = landingbuilder_runtime_sanitize_link($parts[2] ?? '');
				if ($question === '' && $answer === '') {
					continue;
				}

				$faq_items[] = '<details' . (($open_first && $index === 0) ? ' open' : '') . ' style="background:' . html($question_bg, false) . ';border:1px solid ' . html($border_color, false) . ';border-radius:' . $radius . 'px;padding:12px 14px;">'
					. '<summary style="cursor:pointer;list-style:none;font-weight:700;color:' . html($question_color, false) . ';display:flex;align-items:center;gap:8px;">'
					. '<span style="display:inline-flex;width:10px;height:10px;border-radius:999px;background:' . html($accent_color, false) . ';"></span>'
					. html($question ?: 'Вопрос', false)
					. '</summary>'
					. '<div style="margin-top:10px;color:' . html($answer_color, false) . ';line-height:1.65;">' . html($answer ?: 'Ответ будет добавлен позже.', false) . '</div>'
					. ($link ? '<div style="margin-top:10px;"><a href="' . html($link, false) . '" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:' . html($accent_color, false) . ';text-decoration:none;">Подробнее</a></div>' : '')
					. '</details>';
			}

			$grid_template = $layout_mode === 'two' ? 'repeat(2,minmax(0,1fr))' : 'minmax(0,1fr)';
			$body = ''
				. '<section style="padding:' . $padding_y . 'px 28px;background:' . html($section_bg, false) . ';border-radius:' . $radius . 'px;">'
				. '<div style="max-width:1160px;margin:0 auto;">'
				. '<h3 style="margin:0 0 10px;font-size:clamp(24px,3vw,34px);color:' . html($question_color, false) . ';">' . html($heading ?: 'Частые вопросы', false) . '</h3>'
				. ($text ? '<p style="margin:0 0 16px;color:' . html($answer_color, false) . ';line-height:1.6;">' . html($text, false) . '</p>' : '')
				. '<div style="display:grid;grid-template-columns:' . html($grid_template, false) . ';gap:' . $gap . 'px;">' . implode('', $faq_items) . '</div>'
				. '</div>'
				. '</section>';
		} else {
			$body = $surface === 'overlay'
				? '<h3>' . html($heading ?: $title, false) . '</h3><p>' . html($text ?: 'Блок Нордик уже подключен к живой странице. Следующим шагом сюда можно подать реальные props и data bindings.', false) . '</p>'
				: '<h3>' . html($heading ?: $title, false) . '</h3><p>' . html($text ?: 'Runtime-рендер для блока уже подключен. Следующим этапом здесь можно будет показать реальные props и data bindings.', false) . '</p>';
		}

		$meta = '';
		if ($source_key) {
			$meta_title = $block_titles[$source_key] ?? (($node['block_meta']['title'] ?? '') ?: $source_key);
			$meta .= '<div class="lb-node-meta">Источник блока: ' . html($meta_title, false) . '</div>';
		}
		if (!empty($node['block_meta']['summary'])) {
			$meta .= '<div class="lb-node-meta">Смысловой сценарий: ' . html($node['block_meta']['summary'], false) . '</div>';
		}
		if ($notes) {
			$meta .= '<div class="lb-node-note">' . nl2br(html($notes, false)) . '</div>';
		}

		if ($surface === 'site') {
			$classes = ['lb-runtime-block', 'lb-runtime-block--base'];
			if (landingbuilder_runtime_base_autoscale_enabled($context)) {
				$classes[] = 'lb-runtime-block--autoscale';
			}

			return '<div class="' . html(implode(' ', $classes), false) . '" data-source-key="' . html($source_key !== '' ? $source_key : $key, false) . '">' . $body . '</div>';
		}

		if ($surface === 'overlay') {
			return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Блок Нордик</div>' . $body . $meta . '</div></article>';
		}

		return '<article class="lb-node-card lb-node-card--block"><div class="lb-node-label">Блок Нордик</div><div class="lb-node-content">' . $body . $meta . '</div></article>';
	}

	function landingbuilder_render_runtime_widget(array $node, array $context = []) {
		$surface = $context['surface'] ?? 'runtime';
		$widget_name = $node['widget_name'] ?? '';
		$widget_controller = $node['widget_controller'] ?? '';
		$widget_title = $node['label'] ?? 'Системный виджет';
		$widget_options = isset($node['options']) && is_array($node['options']) ? $node['options'] : [];
		$failure_hint = '';

		if (!$widget_name) {
			if ($surface === 'overlay') {
				return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Системный виджет</div><p>Виджет пока не привязан к системному каталогу. Его можно перевыбрать в редакторе страницы.</p></div></article>';
			}

			return '<article class="lb-node-card lb-node-card--widget"><div class="lb-node-label">Системный виджет</div><div class="lb-node-content"><p>Виджет еще не связан с системным каталогом. Выберите его заново в редакторе страницы.</p></div></article>';
		}

		if ($widget_controller === 'users' && $widget_name === 'avatar' && empty($widget_options['menu'])) {
			$failure_hint = ' Для этого виджета нужно выбрать меню с действиями пользователя в настройках виджета.';
		}

		if (($widget_controller === '' || $widget_controller === 'core') && $widget_name === 'menu' && empty($widget_options['menu'])) {
			$failure_hint = ' Для виджета меню нужно выбрать, какое меню показывать, в его настройках.';
		}

		$widget_data = [
			'id'          => !empty($node['widget_id']) ? (int) $node['widget_id'] : abs(crc32((string) ($node['uid'] ?? $widget_name))),
			'name'        => $widget_name,
			'controller'  => $widget_controller,
			'position'    => uniqid('landingbuilder_runtime_', true),
			'class'       => '',
			'class_title' => '',
			'class_wrap'  => '',
			'title'       => $widget_title,
			'is_title'    => false,
			'options'     => $widget_options
		];

		try {
			$html = false;
			$template = cmsTemplate::getInstance();
			$result = cmsCore::getInstance()->runWidget($widget_data);

			if ($result instanceof cmsTemplate) {
				ob_start();
				$template->widgets($widget_data['position'], false, '');
				$html = ob_get_clean();
			}
		} catch (Throwable $exception) {
			$html = false;
		}

		if ($html === false || $html === null || $html === '') {
			$show_widget_fallback = !empty($context['show_widget_fallback']);

			if ($surface === 'site') {
				if (!$show_widget_fallback) {
					return '';
				}

				return '<div class="lb-runtime-widget-fallback"><strong>' . html($widget_title, false) . '</strong><span>Системный виджет недоступен в preview-контексте.' . html($failure_hint, false) . '</span></div>';
			}

			if ($surface === 'overlay') {
				return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Системный виджет</div><p>Виджет ' . html($widget_title, false) . ' не удалось показать в этой зоне. Проверьте его настройки на холсте.' . html($failure_hint, false) . '</p></div></article>';
			}

			return '<article class="lb-node-card lb-node-card--widget"><div class="lb-node-label">Системный виджет</div><div class="lb-node-content"><p>Виджет ' . html($widget_title, false) . ' не удалось вывести в runtime. Проверьте его настройки в редакторе.' . html($failure_hint, false) . '</p></div></article>';
		}

		if ($surface === 'site') {
			return $html;
		}

		if ($surface === 'overlay') {
			return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Системный виджет</div>' . $html . '</div></article>';
		}

		return '<article class="lb-node-card lb-node-card--widget"><div class="lb-node-label">Системный виджет</div><div class="lb-node-content">' . $html . '</div></article>';
	}

	function landingbuilder_render_runtime_node(array $node, array $context = []) {
		$device_type = $context['device_type'] ?? 'desktop';

		if (!landingbuilder_runtime_is_visible($node['device_visibility'] ?? [], $device_type)) {
			return '';
		}

		return ($node['type'] ?? 'block') === 'system_widget'
			? landingbuilder_render_runtime_widget($node, $context)
			: landingbuilder_render_runtime_block($node, $context);
	}

	function landingbuilder_render_runtime_section(array $section, array $context = []) {
		$device_type = $context['device_type'] ?? 'desktop';
		$surface = $context['surface'] ?? 'runtime';
		$theme_context = $context['theme_context'] ?? ['theme' => []];

		if (!landingbuilder_runtime_is_visible($section['visibility'] ?? [], $device_type)) {
			return '';
		}

		$section_theme = landingbuilder_get_section_presentation($section, $theme_context);
		$grid_templates = landingbuilder_runtime_get_section_grid_templates($section);
		$columns_inline_style = '--lb-grid-desktop:' . ($grid_templates['desktop'] ?? 'minmax(0,1fr)')
			. ';--lb-grid-tablet:' . ($grid_templates['tablet'] ?? ($grid_templates['desktop'] ?? 'minmax(0,1fr)'))
			. ';--lb-grid-mobile:' . ($grid_templates['mobile'] ?? ($grid_templates['tablet'] ?? 'minmax(0,1fr)'))
			. ';';
		$zone_key = $section['zone_key'] ?? ($context['zone_key'] ?? '');
		$section_autoscale_base_blocks = !empty($section['settings']) && is_array($section['settings'])
			? (($section['settings']['autoscale_base_blocks'] ?? false) === true)
			: false;
		$active_units = landingbuilder_runtime_resolve_section_units($section, $device_type);
		$is_stacked_active = landingbuilder_runtime_is_stacked_for_device($section, $device_type);
		$visible_columns_count = landingbuilder_runtime_count_visible_columns($section, $device_type);
		$section_autoscale_wide = $section_autoscale_base_blocks && $visible_columns_count > 1;

		ob_start();
		if ($surface === 'overlay') {
			$columns_count = count($section['columns'] ?? []);
			?>
			<section class="lb-overlay-section card shadow-sm mb-4 <?php html($section_theme['class']); ?>" data-style-preset="<?php html($section_theme['style_preset']); ?>" data-background-tone="<?php html($section_theme['background_tone']); ?>" data-container-preset="<?php html($section_theme['container_preset']); ?>" data-spacing-preset="<?php html($section_theme['spacing_preset']); ?>" data-slot-key="<?php html($zone_key ?: ($context['slot_key'] ?? '')); ?>">
				<div class="card-body p-4">
					<div class="lb-overlay-section__inner">
						<div class="lb-overlay-section__head">
							<div>
								<div class="lb-overlay-section__kicker">Секция Нордик</div>
								<h3 class="lb-overlay-section__title"><?php html($section['title'] ?? 'Секция'); ?></h3>
							</div>
							<?php if ($zone_key) { ?>
								<div class="lb-overlay-pill">Зона: <?php html($zone_key); ?></div>
							<?php } ?>
						</div>

						<div class="lb-overlay-columns row">
							<?php foreach (($section['columns'] ?? []) as $column_index => $column) { ?>
								<?php if (!landingbuilder_runtime_is_visible($column['visibility'] ?? [], $device_type)) { continue; } ?>
								<div class="<?php html(landingbuilder_get_runtime_overlay_column_class($section['layout'] ?? '1col', $column_index, $columns_count)); ?> mb-3">
									<div class="lb-overlay-column-title"><?php html($column['title'] ?? ('Колонка ' . ($column_index + 1))); ?></div>
									<?php if (empty($column['nodes'])) { ?>
										<div class="lb-empty-zone">В этой колонке пока нет элементов.</div>
									<?php } else { ?>
										<?php foreach ($column['nodes'] as $node) { ?>
											<?php echo landingbuilder_render_runtime_node($node, $context); ?>
										<?php } ?>
									<?php } ?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</section>
			<?php
		} elseif ($surface === 'site') {
			$grid_class = landingbuilder_get_runtime_layout_class($section['layout'] ?? '1col');
			$section_class = 'lb-section ' . $grid_class . ' ' . $section_theme['class']
				. ($section_autoscale_base_blocks ? ' lb-section--autoscale-base-blocks' : '')
				. ($section_autoscale_wide ? ' lb-section--autoscale-wide' : '');
			?>
			<section class="<?php html(trim($section_class)); ?>" data-style-preset="<?php html($section_theme['style_preset']); ?>" data-background-tone="<?php html($section_theme['background_tone']); ?>" data-container-preset="<?php html($section_theme['container_preset']); ?>" data-spacing-preset="<?php html($section_theme['spacing_preset']); ?>" data-slot-key="<?php html($zone_key ?: ($context['slot_key'] ?? '')); ?>">
				<div class="lb-section-inner">
					<div class="lb-columns <?php html($grid_class); ?>" style="<?php html($columns_inline_style); ?>">
						<?php foreach (($section['columns'] ?? []) as $column_index => $column) { ?>
							<?php if (!landingbuilder_runtime_is_visible($column['visibility'] ?? [], $device_type)) { continue; } ?>
							<div class="lb-column<?php if (!empty($column['settings']['css_class'])) { ?> <?php html($column['settings']['css_class']); ?><?php } ?>">
								<?php foreach (($column['nodes'] ?? []) as $node) { ?>
									<?php
									$column_units = $is_stacked_active ? 12 : (int) ($active_units[$column_index] ?? 0);
									echo landingbuilder_render_runtime_node($node, array_merge($context, [
										'column_units' => $column_units,
										'section_autoscale_base_blocks' => $section_autoscale_base_blocks
									]));
									?>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</section>
			<?php
		} else {
			$grid_class = landingbuilder_get_runtime_layout_class($section['layout'] ?? '1col');
			$section_class = 'lb-section ' . $grid_class . ' ' . $section_theme['class']
				. ($section_autoscale_base_blocks ? ' lb-section--autoscale-base-blocks' : '')
				. ($section_autoscale_wide ? ' lb-section--autoscale-wide' : '');
			?>
			<section class="<?php html(trim($section_class)); ?>" data-style-preset="<?php html($section_theme['style_preset']); ?>" data-background-tone="<?php html($section_theme['background_tone']); ?>" data-container-preset="<?php html($section_theme['container_preset']); ?>" data-spacing-preset="<?php html($section_theme['spacing_preset']); ?>" data-slot-key="<?php html($zone_key ?: ($context['slot_key'] ?? '')); ?>">
				<div class="lb-section-inner">
					<div class="lb-section-head">
						<div>
							<div class="lb-section-kicker">Секция конструктора</div>
							<h2><?php html($section['title']); ?></h2>
						</div>
						<?php if ($zone_key) { ?>
							<div class="lb-zone-pill">Зона: <?php html($zone_key); ?></div>
						<?php } ?>
					</div>
					<div class="lb-columns <?php html($grid_class); ?>" style="<?php html($columns_inline_style); ?>">
						<?php foreach (($section['columns'] ?? []) as $column_index => $column) { ?>
							<?php if (!landingbuilder_runtime_is_visible($column['visibility'] ?? [], $device_type)) { continue; } ?>
							<div class="lb-column<?php if (!empty($column['settings']['css_class'])) { ?> <?php html($column['settings']['css_class']); ?><?php } ?>">
								<div class="lb-column-title"><?php html($column['title']); ?></div>
								<?php foreach (($column['nodes'] ?? []) as $node) { ?>
									<?php
									$column_units = $is_stacked_active ? 12 : (int) ($active_units[$column_index] ?? 0);
									echo landingbuilder_render_runtime_node($node, array_merge($context, [
										'column_units' => $column_units,
										'section_autoscale_base_blocks' => $section_autoscale_base_blocks
									]));
									?>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</section>
			<?php
		}

		return ob_get_clean();
	}

	function landingbuilder_render_runtime_zone(array $zone, array $context = []) {
		$slot_map = !empty($context['slot_map']) && is_array($context['slot_map']) ? $context['slot_map'] : [];
		$slot_key = $zone['slot_key'] ?? ($zone['key'] ?? '');
		$slot = $slot_map[$slot_key] ?? [];
		$native_label = $zone['native_label'] ?? ($slot['native_label'] ?? '');

		ob_start();
		?>
		<section class="lb-runtime-zone lb-runtime-zone--<?php html($zone['kind']); ?>" data-slot-key="<?php html($slot_key); ?>" data-render-mode="<?php html($slot['render_mode'] ?? (($zone['kind'] ?? 'builder') === 'native' ? 'native' : 'builder')); ?>">
			<div class="lb-runtime-zone-head">
				<div>
					<div class="lb-zone-kind"><?php html(($zone['kind'] ?? 'builder') === 'native' ? 'Системная зона' : 'Зона конструктора'); ?></div>
					<h2><?php html($zone['title']); ?></h2>
				</div>
				<?php if (!empty($zone['description'])) { ?>
					<p><?php html($zone['description']); ?></p>
				<?php } ?>
			</div>
			<div class="lb-runtime-zone-body">
				<?php if (($zone['kind'] ?? 'builder') === 'native') { ?>
					<div class="lb-native-placeholder">
						<div class="lb-native-title"><?php html($zone['title']); ?></div>
						<p><?php html($native_label); ?></p>
					</div>
				<?php } else { ?>
					<?php if (empty($zone['sections'])) { ?>
						<div class="lb-empty-zone">Секции для этой зоны пока не добавлены.</div>
					<?php } else { ?>
						<?php foreach ($zone['sections'] as $section) { ?>
							<?php echo landingbuilder_render_runtime_section($section, array_merge($context, ['zone_key' => $zone['key'] ?? '', 'slot_key' => $slot_key, 'surface' => 'runtime'])); ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
			</div>
		</section>
		<?php

		return ob_get_clean();
	}

	function landingbuilder_render_runtime_zone_sections(array $zone, array $context = []) {
		$html = '';
		$surface = $context['surface'] ?? 'overlay';

		foreach (($zone['sections'] ?? []) as $section) {
			$html .= landingbuilder_render_runtime_section($section, array_merge($context, [
				'zone_key' => $zone['key'] ?? '',
				'slot_key' => $zone['slot_key'] ?? ($zone['key'] ?? ''),
				'surface'  => $surface
			]));
		}

		return $html;
	}
}