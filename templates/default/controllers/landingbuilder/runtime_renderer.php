<?php

if (!function_exists('landingbuilder_get_runtime_block_titles')) {

	function landingbuilder_get_runtime_block_titles() {
		return [
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
		$title = $block_titles[$key] ?? ($node['label'] ?: 'Блок');
		$notes = trim((string) ($node['notes'] ?? ''));
		$source_key = trim((string) ($node['source_key'] ?? ''));

		$body = '';
		if ($key === 'core.hero-heading') {
			$body = '<h2>Главный экран страницы</h2><p>Крупный вводный блок для основного обещания, подзаголовка и первого впечатления.</p>';
		} elseif ($key === 'core.hero-actions') {
			$body = '<h2>Главное действие</h2><p>Зона для призыва к действию, кнопок и короткой поясняющей строки.</p>';
		} elseif ($key === 'core.cards-grid') {
			$body = '<h3>Карточки</h3><p>Сетка для услуг, тарифов, преимуществ или тематических подборок.</p>';
		} elseif ($key === 'core.feature-list') {
			$body = '<h3>Преимущества</h3><ul><li>Короткие тезисы</li><li>Простая визуальная подача</li><li>Подходит для доверительных аргументов</li></ul>';
		} elseif ($key === 'ads.category-header') {
			$body = $surface === 'overlay'
				? '<h2>Шапка категории</h2><p>Эта секция уже встраивается в живую страницу и может усиливать контекст категории перед списком объявлений.</p>'
				: '<h2>Категория объявлений</h2><p>Шапка категории с контекстом страницы, подводкой и визуальным акцентом.</p>';
		} elseif ($key === 'ads.filter-bar') {
			$body = $surface === 'overlay'
				? '<h3>Панель отбора</h3><p>Здесь может жить дополнительная панель фильтров, подсказки по поиску или короткий поясняющий блок.</p>'
				: '<h3>Фильтры категории</h3><p>Зона для панели отбора и быстрого уточнения списка.</p>';
		} elseif ($key === 'profile.cover-hero') {
			$body = '<h2>Обложка профиля</h2><p>Крупный верхний блок для имени, описания и визуального образа профиля.</p>';
		} elseif ($key === 'profile.quick-stats') {
			$body = '<h3>Статистика профиля</h3><p>Короткие показатели и важные цифры в одном месте.</p>';
		} else {
			$body = $surface === 'overlay'
				? '<h3>' . html($title, false) . '</h3><p>Блок Нордик уже подключен к живой странице. Следующим шагом сюда можно подать реальные props и data bindings.</p>'
				: '<h3>' . html($title, false) . '</h3><p>Runtime-рендер для блока уже подключен. Следующим этапом здесь можно будет показать реальные props и data bindings.</p>';
		}

		$meta = '';
		if ($source_key) {
			$meta_title = $block_titles[$source_key] ?? $source_key;
			$meta .= '<div class="lb-node-meta">Источник блока: ' . html($meta_title, false) . '</div>';
		}
		if ($notes) {
			$meta .= '<div class="lb-node-note">' . nl2br(html($notes, false)) . '</div>';
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
			if ($surface === 'overlay') {
				return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Системный виджет</div><p>Виджет ' . html($widget_title, false) . ' не удалось показать в этой зоне. Проверьте его настройки на холсте.' . html($failure_hint, false) . '</p></div></article>';
			}

			return '<article class="lb-node-card lb-node-card--widget"><div class="lb-node-label">Системный виджет</div><div class="lb-node-content"><p>Виджет ' . html($widget_title, false) . ' не удалось вывести в runtime. Проверьте его настройки в редакторе.' . html($failure_hint, false) . '</p></div></article>';
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

		foreach (($zone['sections'] ?? []) as $section) {
			$html .= landingbuilder_render_runtime_section($section, array_merge($context, [
				'zone_key' => $zone['key'] ?? '',
				'slot_key' => $zone['slot_key'] ?? ($zone['key'] ?? ''),
				'surface'  => 'overlay'
			]));
		}

		return $html;
	}
}