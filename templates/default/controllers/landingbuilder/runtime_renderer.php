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
			'profile.quick-stats'   => 'Короткая статистика профиля'
		];
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
			return $body;
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
			if ($surface === 'site') {
				return '';
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
		$zone_key = $section['zone_key'] ?? ($context['zone_key'] ?? '');

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
			?>
			<section class="lb-section <?php html($grid_class); ?> <?php html($section_theme['class']); ?>" data-style-preset="<?php html($section_theme['style_preset']); ?>" data-background-tone="<?php html($section_theme['background_tone']); ?>" data-container-preset="<?php html($section_theme['container_preset']); ?>" data-spacing-preset="<?php html($section_theme['spacing_preset']); ?>" data-slot-key="<?php html($zone_key ?: ($context['slot_key'] ?? '')); ?>">
				<div class="lb-section-inner">
					<div class="lb-columns <?php html($grid_class); ?>">
						<?php foreach (($section['columns'] ?? []) as $column) { ?>
							<?php if (!landingbuilder_runtime_is_visible($column['visibility'] ?? [], $device_type)) { continue; } ?>
							<div class="lb-column<?php if (!empty($column['settings']['css_class'])) { ?> <?php html($column['settings']['css_class']); ?><?php } ?>">
								<?php foreach (($column['nodes'] ?? []) as $node) { ?>
									<?php echo landingbuilder_render_runtime_node($node, $context); ?>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</section>
			<?php
		} else {
			$grid_class = landingbuilder_get_runtime_layout_class($section['layout'] ?? '1col');
			?>
			<section class="lb-section <?php html($grid_class); ?> <?php html($section_theme['class']); ?>" data-style-preset="<?php html($section_theme['style_preset']); ?>" data-background-tone="<?php html($section_theme['background_tone']); ?>" data-container-preset="<?php html($section_theme['container_preset']); ?>" data-spacing-preset="<?php html($section_theme['spacing_preset']); ?>" data-slot-key="<?php html($zone_key ?: ($context['slot_key'] ?? '')); ?>">
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
					<div class="lb-columns <?php html($grid_class); ?>">
						<?php foreach (($section['columns'] ?? []) as $column) { ?>
							<?php if (!landingbuilder_runtime_is_visible($column['visibility'] ?? [], $device_type)) { continue; } ?>
							<div class="lb-column<?php if (!empty($column['settings']['css_class'])) { ?> <?php html($column['settings']['css_class']); ?><?php } ?>">
								<div class="lb-column-title"><?php html($column['title']); ?></div>
								<?php foreach (($column['nodes'] ?? []) as $node) { ?>
									<?php echo landingbuilder_render_runtime_node($node, $context); ?>
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