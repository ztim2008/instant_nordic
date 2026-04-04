<?php

$adapter = $runtime['adapter'];
$zones = $runtime['zones'];
$device_type = $runtime['device_type'] ?? 'desktop';
$is_preview = !empty($runtime['is_preview']);

$page_mode_titles = [
	'full_takeover'  => 'Полностью своя страница',
	'hybrid_overlay' => 'Поверх существующей страницы',
	'zone_injection' => 'Встраивание в зону страницы',
	'data_only'      => 'Только данные для блоков'
];

$block_titles = [
	'core.hero-heading'     => 'Главный экран с заголовком',
	'core.hero-actions'     => 'Главный экран с кнопками',
	'core.cards-grid'       => 'Сетка карточек',
	'core.feature-list'     => 'Список преимуществ',
	'ads.category-header'   => 'Шапка категории объявлений',
	'ads.filter-bar'        => 'Панель фильтров',
	'profile.cover-hero'    => 'Обложка профиля',
	'profile.quick-stats'   => 'Короткая статистика профиля'
];

$layout_class_map = [
	'1col'               => 'lb-grid-1',
	'2col_equal'         => 'lb-grid-2',
	'2col_sidebar_left'  => 'lb-grid-sidebar-left',
	'2col_sidebar_right' => 'lb-grid-sidebar-right',
	'3col_equal'         => 'lb-grid-3'
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

$render_block = static function (array $node) use ($block_titles) {
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
		$body = '<h2>Категория объявлений</h2><p>Шапка категории с контекстом страницы, подводкой и визуальным акцентом.</p>';
	} elseif ($key === 'ads.filter-bar') {
		$body = '<h3>Фильтры категории</h3><p>Зона для панели отбора и быстрого уточнения списка.</p>';
	} elseif ($key === 'profile.cover-hero') {
		$body = '<h2>Обложка профиля</h2><p>Крупный верхний блок для имени, описания и визуального образа профиля.</p>';
	} elseif ($key === 'profile.quick-stats') {
		$body = '<h3>Статистика профиля</h3><p>Короткие показатели и важные цифры в одном месте.</p>';
	} else {
		$body = '<h3>' . html($title, false) . '</h3><p>Runtime-рендер для блока уже подключен. Следующим этапом здесь можно будет показать реальные props и data bindings.</p>';
	}

	$meta = '';
	if ($source_key) {
		$meta_title = $block_titles[$source_key] ?? $source_key;
		$meta .= '<div class="lb-node-meta">Источник блока: ' . html($meta_title, false) . '</div>';
	}
	if ($notes) {
		$meta .= '<div class="lb-node-note">' . nl2br(html($notes, false)) . '</div>';
	}

	return '<article class="lb-node-card lb-node-card--block"><div class="lb-node-label">Блок Нордик</div><div class="lb-node-content">' . $body . $meta . '</div></article>';
};

$render_widget = static function (array $node) {
	$widget_name = $node['widget_name'] ?? '';
	$widget_controller = $node['widget_controller'] ?? '';
	$widget_title = $node['label'] ?? 'Системный виджет';

	if (!$widget_name) {
		return '<article class="lb-node-card lb-node-card--widget"><div class="lb-node-label">Системный виджет</div><div class="lb-node-content"><p>Виджет еще не связан с системным каталогом. Выберите его заново в редакторе страницы.</p></div></article>';
	}

	$widget_data = [
		'id'         => !empty($node['widget_id']) ? (int) $node['widget_id'] : abs(crc32((string) ($node['uid'] ?? $widget_name))),
		'name'       => $widget_name,
		'controller' => $widget_controller,
		'class'      => '',
		'class_title'=> '',
		'class_wrap' => '',
		'title'      => $widget_title,
		'options'    => isset($node['options']) && is_array($node['options']) ? $node['options'] : []
	];

	try {
		$html = cmsCore::getInstance()->runWidget($widget_data);
	} catch (Throwable $exception) {
		$html = false;
	}
	if ($html === false || $html === null || $html === '') {
		return '<article class="lb-node-card lb-node-card--widget"><div class="lb-node-label">Системный виджет</div><div class="lb-node-content"><p>Виджет ' . html($widget_title, false) . ' не удалось вывести в runtime. Проверьте его настройки в редакторе.</p></div></article>';
	}

	return '<article class="lb-node-card lb-node-card--widget"><div class="lb-node-label">Системный виджет</div><div class="lb-node-content">' . $html . '</div></article>';
};

$render_section = static function (array $section) use ($device_type, $layout_class_map, $is_visible, $render_block, $render_widget) {
	if (!$is_visible($section['visibility'] ?? [], $device_type)) {
		return '';
	}

	ob_start();
	$grid_class = $layout_class_map[$section['layout']] ?? 'lb-grid-1';
	?>
	<section class="lb-section <?php html($grid_class); ?><?php if (!empty($section['settings']['css_class'])) { ?> <?php html($section['settings']['css_class']); ?><?php } ?>">
		<div class="lb-section-head">
			<div>
				<div class="lb-section-kicker">Секция конструктора</div>
				<h2><?php html($section['title']); ?></h2>
			</div>
			<?php if (!empty($section['zone_key'])) { ?>
				<div class="lb-zone-pill">Зона: <?php html($section['zone_key']); ?></div>
			<?php } ?>
		</div>
		<div class="lb-columns <?php html($grid_class); ?>">
			<?php foreach ($section['columns'] as $column) { ?>
				<?php if (!$is_visible($column['visibility'] ?? [], $device_type)) { continue; } ?>
				<div class="lb-column<?php if (!empty($column['settings']['css_class'])) { ?> <?php html($column['settings']['css_class']); ?><?php } ?>">
					<div class="lb-column-title"><?php html($column['title']); ?></div>
					<?php foreach ($column['nodes'] as $node) { ?>
						<?php if (!$is_visible($node['device_visibility'] ?? [], $device_type)) { continue; } ?>
						<?php echo ($node['type'] ?? 'block') === 'system_widget' ? $render_widget($node) : $render_block($node); ?>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
};

$render_zone = static function (array $zone) use ($render_section) {
	ob_start();
	?>
	<section class="lb-runtime-zone lb-runtime-zone--<?php html($zone['kind']); ?>">
		<div class="lb-runtime-zone-head">
			<div>
				<div class="lb-zone-kind"><?php html($zone['kind'] === 'native' ? 'Системная зона' : 'Зона конструктора'); ?></div>
				<h2><?php html($zone['title']); ?></h2>
			</div>
			<?php if (!empty($zone['description'])) { ?>
				<p><?php html($zone['description']); ?></p>
			<?php } ?>
		</div>
		<div class="lb-runtime-zone-body">
			<?php if ($zone['kind'] === 'native') { ?>
				<div class="lb-native-placeholder">
					<div class="lb-native-title"><?php html($zone['title']); ?></div>
					<p><?php html($zone['native_label']); ?></p>
				</div>
			<?php } else { ?>
				<?php if (empty($zone['sections'])) { ?>
					<div class="lb-empty-zone">Секции для этой зоны пока не добавлены.</div>
				<?php } else { ?>
					<?php foreach ($zone['sections'] as $section) { echo $render_section($section); } ?>
				<?php } ?>
			<?php } ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
};

ob_start();
?>
<style>
	.lb-runtime-page {
		max-width: 1240px;
		margin: 0 auto;
		padding: 32px 20px 56px;
	}
	.lb-runtime-hero {
		padding: 28px;
		border-radius: 24px;
		background: linear-gradient(135deg, #f4f6f8 0%, #ffffff 60%, #eef3f8 100%);
		border: 1px solid #dbe3ea;
		box-shadow: 0 18px 48px rgba(19, 41, 61, 0.08);
		margin-bottom: 28px;
	}
	.lb-runtime-kicker {
		font-size: 12px;
		letter-spacing: 0.12em;
		text-transform: uppercase;
		color: #5e7c92;
		margin-bottom: 12px;
	}
	.lb-runtime-hero h1 {
		margin: 0 0 10px;
		font-size: 40px;
		line-height: 1.1;
		color: #163040;
	}
	.lb-runtime-meta {
		display: flex;
		gap: 10px;
		flex-wrap: wrap;
		margin-top: 18px;
	}
	.lb-runtime-pill {
		display: inline-flex;
		align-items: center;
		padding: 8px 12px;
		border-radius: 999px;
		background: #ffffff;
		border: 1px solid #d7e0e7;
		font-size: 13px;
		color: #335168;
	}
	.lb-preview-note {
		margin-top: 18px;
		padding: 12px 14px;
		border-radius: 14px;
		background: #fff7df;
		border: 1px solid #f1d690;
		color: #6c5618;
	}
	.lb-runtime-layout {
		display: grid;
		gap: 20px;
	}
	.lb-runtime-zone {
		background: #fff;
		border-radius: 22px;
		border: 1px solid #dce4ea;
		padding: 22px;
		box-shadow: 0 12px 32px rgba(18, 36, 52, 0.06);
	}
	.lb-runtime-zone-head {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 20px;
		margin-bottom: 18px;
	}
	.lb-runtime-zone-head h2 {
		margin: 4px 0 0;
		font-size: 22px;
		color: #1c3344;
	}
	.lb-runtime-zone-head p,
	.lb-native-placeholder p {
		margin: 0;
		max-width: 520px;
		color: #5b7282;
	}
	.lb-zone-kind,
	.lb-section-kicker,
	.lb-node-label,
	.lb-column-title,
	.lb-native-title {
		font-size: 12px;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #648197;
	}
	.lb-native-placeholder,
	.lb-empty-zone {
		padding: 18px;
		border-radius: 16px;
		border: 1px dashed #b8c8d4;
		background: #f7fafc;
	}
	.lb-section {
		padding: 18px;
		border-radius: 18px;
		background: #fdfefe;
		border: 1px solid #dfe8ee;
		margin-bottom: 18px;
	}
	.lb-section:last-child {
		margin-bottom: 0;
	}
	.lb-section-head {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		gap: 18px;
		margin-bottom: 16px;
	}
	.lb-section-head h2 {
		margin: 4px 0 0;
		font-size: 24px;
		color: #142c3d;
	}
	.lb-zone-pill {
		padding: 8px 12px;
		border-radius: 999px;
		background: #edf5fb;
		color: #2a5979;
		font-size: 13px;
	}
	.lb-columns {
		display: grid;
		gap: 16px;
	}
	.lb-grid-1 {
		grid-template-columns: minmax(0, 1fr);
	}
	.lb-grid-2,
	.lb-grid-sidebar-left,
	.lb-grid-sidebar-right {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}
	.lb-grid-sidebar-left {
		grid-template-columns: minmax(220px, 0.8fr) minmax(0, 1.6fr);
	}
	.lb-grid-sidebar-right {
		grid-template-columns: minmax(0, 1.6fr) minmax(220px, 0.8fr);
	}
	.lb-grid-3 {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}
	.lb-column {
		padding: 16px;
		border-radius: 16px;
		background: #f7fafc;
		border: 1px solid #dbe6ee;
	}
	.lb-node-card {
		margin-top: 12px;
		padding: 16px;
		border-radius: 16px;
		background: #ffffff;
		border: 1px solid #dce5ec;
		box-shadow: 0 8px 20px rgba(22, 38, 52, 0.05);
	}
	.lb-node-card--block {
		border-left: 4px solid #3f8fb5;
	}
	.lb-node-card--widget {
		border-left: 4px solid #2a6752;
	}
	.lb-node-content h2,
	.lb-node-content h3 {
		margin: 8px 0 10px;
		color: #173042;
	}
	.lb-node-content p,
	.lb-node-content li {
		color: #566f80;
	}
	.lb-node-meta,
	.lb-node-note {
		margin-top: 10px;
		font-size: 13px;
		color: #6b7f8d;
	}
	@media (max-width: 900px) {
		.lb-runtime-page {
			padding: 24px 14px 48px;
		}
		.lb-runtime-hero h1 {
			font-size: 30px;
		}
		.lb-grid-2,
		.lb-grid-sidebar-left,
		.lb-grid-sidebar-right,
		.lb-grid-3 {
			grid-template-columns: minmax(0, 1fr);
		}
		.lb-runtime-zone-head,
		.lb-section-head {
			flex-direction: column;
		}
	}
</style>

<div class="lb-runtime-page">
	<header class="lb-runtime-hero">
		<div class="lb-runtime-kicker">Нордик frontend runtime</div>
		<h1><?php html($page['title']); ?></h1>
		<p><?php html($adapter['description']); ?></p>
		<div class="lb-runtime-meta">
			<div class="lb-runtime-pill">Режим: <?php html($page_mode_titles[$page['mode']] ?? $page['mode']); ?></div>
			<div class="lb-runtime-pill">Adapter: <?php html($adapter['title']); ?></div>
			<div class="lb-runtime-pill">Ключ страницы: <?php html($page['key']); ?></div>
			<div class="lb-runtime-pill">Устройство: <?php html($device_type); ?></div>
		</div>
		<?php if ($is_preview) { ?>
			<div class="lb-preview-note">Открыт preview-режим. Страница еще не опубликована, поэтому runtime доступен только администратору.</div>
		<?php } ?>
	</header>

	<div class="lb-runtime-layout">
		<?php foreach ($zones as $zone) { ?>
			<?php if (($zone['kind'] ?? 'builder') === 'native') {
				$zone['native_label'] = $adapter['native_content_label'];
			} ?>
			<?php echo $render_zone($zone); ?>
		<?php } ?>
	</div>
</div>

<?php echo ob_get_clean(); ?>