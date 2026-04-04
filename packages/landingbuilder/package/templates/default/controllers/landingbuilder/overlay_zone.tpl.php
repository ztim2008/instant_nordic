<?php

$device_type = cmsRequest::getDeviceType();

$block_titles = [
	'core.hero-heading'   => 'Главный экран с заголовком',
	'core.hero-actions'   => 'Главный экран с кнопками',
	'core.cards-grid'     => 'Сетка карточек',
	'core.feature-list'   => 'Список преимуществ',
	'ads.category-header' => 'Шапка категории объявлений',
	'ads.filter-bar'      => 'Панель фильтров',
	'profile.cover-hero'  => 'Обложка профиля',
	'profile.quick-stats' => 'Короткая статистика профиля'
];

$is_visible = static function ($visibility, $device) {
	if (!is_array($visibility)) {
		return true;
	}

	if (!array_key_exists($device, $visibility)) {
		return true;
	}

	return $visibility[$device] !== false;
};

$get_column_class = static function ($layout, $column_index, $columns_count) {
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
};

$render_block = static function (array $node) use ($block_titles) {
	$key = $node['source_key'] ?: $node['label'];
	$title = $block_titles[$key] ?? ($node['label'] ?: 'Блок');
	$notes = trim((string) ($node['notes'] ?? ''));

	$body = '';
	if ($key === 'ads.category-header') {
		$body = '<h2>Шапка категории</h2><p>Эта секция уже встраивается в живую страницу и может усиливать контекст категории перед списком объявлений.</p>';
	} elseif ($key === 'ads.filter-bar') {
		$body = '<h3>Панель отбора</h3><p>Здесь может жить дополнительная панель фильтров, подсказки по поиску или короткий поясняющий блок.</p>';
	} elseif ($key === 'core.cards-grid') {
		$body = '<h3>Сетка карточек</h3><p>Подходит для тематических подборок, выгод и коротких промо-секций.</p>';
	} elseif ($key === 'core.feature-list') {
		$body = '<h3>Список преимуществ</h3><p>Зона для доверительных аргументов, преимуществ категории и поясняющих тезисов.</p>';
	} else {
		$body = '<h3>' . html($title, false) . '</h3><p>Блок Нордик уже подключен к живой странице. Следующим шагом сюда можно подать реальные props и data bindings.</p>';
	}

	$meta = '<div class="lb-node-meta">Источник блока: ' . html($title, false) . '</div>';
	if ($notes) {
		$meta .= '<div class="lb-node-note">' . nl2br(html($notes, false)) . '</div>';
	}

	return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Блок Нордик</div>' . $body . $meta . '</div></article>';
};

$render_widget = static function (array $node) {
	$widget_name = $node['widget_name'] ?? '';
	$widget_controller = $node['widget_controller'] ?? '';
	$widget_title = $node['label'] ?? 'Системный виджет';

	if (!$widget_name) {
		return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Системный виджет</div><p>Виджет пока не привязан к системному каталогу. Его можно перевыбрать в редакторе страницы.</p></div></article>';
	}

	$widget_data = [
		'id'          => !empty($node['widget_id']) ? (int) $node['widget_id'] : abs(crc32((string) ($node['uid'] ?? $widget_name))),
		'name'        => $widget_name,
		'controller'  => $widget_controller,
		'class'       => '',
		'class_title' => '',
		'class_wrap'  => '',
		'title'       => $widget_title,
		'options'     => isset($node['options']) && is_array($node['options']) ? $node['options'] : []
	];

	try {
		$html = cmsCore::getInstance()->runWidget($widget_data);
	} catch (Throwable $exception) {
		$html = false;
	}

	if ($html === false || $html === null || $html === '') {
		return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Системный виджет</div><p>Виджет ' . html($widget_title, false) . ' не удалось показать в этой зоне. Проверьте его настройки на холсте.</p></div></article>';
	}

	return '<article class="lb-node-card"><div class="lb-node-card__body"><div class="lb-node-label">Системный виджет</div>' . $html . '</div></article>';
};

if (empty($zone['sections'])) {
	return;
}
?>
<div class="lb-overlay-zone lb-overlay-zone--<?php html($zone['key'] ?? 'builder'); ?>">
	<div class="lb-overlay-zone__head">
		<div>
			<div class="lb-overlay-zone__kicker">Живая зона конструктора</div>
			<h2 class="lb-overlay-zone__title"><?php html($zone['title'] ?? 'Зона конструктора'); ?></h2>
			<?php if (!empty($zone['description'])) { ?>
				<p class="lb-overlay-zone__desc"><?php html($zone['description']); ?></p>
			<?php } ?>
		</div>
		<div class="lb-overlay-pill">Страница: <?php html($page['title'] ?? $page['key'] ?? 'Нордик'); ?></div>
	</div>

	<?php foreach ($zone['sections'] as $section) { ?>
		<?php if (!$is_visible($section['visibility'] ?? [], $device_type)) { continue; } ?>
		<section class="lb-overlay-section card shadow-sm mb-4">
			<div class="card-body p-4">
				<div class="lb-overlay-section__head">
					<div>
						<div class="lb-overlay-section__kicker">Секция Нордик</div>
						<h3 class="lb-overlay-section__title"><?php html($section['title'] ?? 'Секция'); ?></h3>
					</div>
					<?php if (!empty($section['zone_key'])) { ?>
						<div class="lb-overlay-pill">Зона: <?php html($section['zone_key']); ?></div>
					<?php } ?>
				</div>

				<div class="row">
					<?php $columns_count = count($section['columns'] ?? []); ?>
					<?php foreach (($section['columns'] ?? []) as $column_index => $column) { ?>
						<?php if (!$is_visible($column['visibility'] ?? [], $device_type)) { continue; } ?>
						<div class="<?php html($get_column_class($section['layout'] ?? '1col', $column_index, $columns_count)); ?> mb-3">
							<div class="lb-overlay-column-title"><?php html($column['title'] ?? ('Колонка ' . ($column_index + 1))); ?></div>
							<?php if (empty($column['nodes'])) { ?>
								<div class="lb-empty-zone">В этой колонке пока нет элементов.</div>
							<?php } else { ?>
								<?php foreach ($column['nodes'] as $node) { ?>
									<?php if (!$is_visible($node['device_visibility'] ?? [], $device_type)) { continue; } ?>
									<?php echo ($node['type'] ?? 'block') === 'system_widget' ? $render_widget($node) : $render_block($node); ?>
								<?php } ?>
							<?php } ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</section>
	<?php } ?>
</div>