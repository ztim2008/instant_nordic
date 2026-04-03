<?php

$device_titles = [
    'desktop' => 'Компьютер',
    'tablet'  => 'Планшет',
    'mobile'  => 'Телефон'
];

$page_mode_titles = [
    'full_takeover'  => 'Полностью своя страница',
    'hybrid_overlay' => 'Поверх существующей страницы',
    'zone_injection' => 'Встраивание в зону страницы',
    'data_only'      => 'Только данные для блоков'
];

$page_status_titles = [
    'draft'     => 'Черновик',
    'prototype' => 'Прототип',
    'idea'      => 'Идея',
    'published' => 'Опубликовано'
];

$layout_titles = [
    '1col'               => 'Одна колонка',
    '2col_equal'         => 'Две равные колонки',
    '2col_sidebar_left'  => 'Узкая колонка слева',
    '2col_sidebar_right' => 'Узкая колонка справа',
    '3col_equal'         => 'Три равные колонки'
];

$node_type_titles = [
    'block'         => 'Блок',
    'system_widget' => 'Системный виджет'
];

$this->setPageTitle('Редактор страницы: ' . $page['title']);
$this->setMenuItems('backend', $menu);
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Страницы', href_to('admin', 'controllers', ['edit', 'landingbuilder', 'pages']));
$this->addBreadcrumb($page['title']);

$this->addToolButton([
    'class' => 'save',
    'title' => 'Сохранить',
    'href'  => '#'
]);

$this->addToolButton([
    'class' => 'view',
    'title' => 'Предпросмотр',
    'href'  => '#'
]);

?>
<div class="padded">
    <h3><?php html($page['title']); ?></h3>
    <p>Ключ страницы: <code><?php html($page['key']); ?></code> | Режим: <?php html($page_mode_titles[$page['mode']] ?? $page['mode']); ?> | Статус: <?php html($page_status_titles[$page['status']] ?? $page['status']); ?></p>
    <p>Служебные адреса API: <code><?php html($screen['api']['widgets_catalog_url']); ?></code> | <code><?php html($screen['api']['widget_options_url']); ?></code> | <code><?php html($screen['api']['canvas_save_url']); ?></code></p>

    <p><strong>Устройства:</strong>
        <?php foreach ($screen['devices'] as $device) { ?>
            <span style="margin-right: 8px;"><?php html($device_titles[$device] ?? $device); ?></span>
        <?php } ?>
    </p>

    <div style="display:flex; gap:16px; align-items:flex-start;">
        <div style="width:24%;">
            <h4>Левая панель</h4>
            <ul>
                <?php foreach ($screen['left_tabs'] as $tab) { ?>
                    <li><?php html($tab); ?></li>
                <?php } ?>
            </ul>
        </div>
        <div style="width:52%;">
            <h4>Макет страницы</h4>
            <?php foreach ($screen['sections'] as $section) { ?>
                <div style="border:1px solid #ccc; padding:12px; margin-bottom:12px;">
                    <p><strong><?php html($section['title']); ?></strong> | схема: <?php html($layout_titles[$section['layout']] ?? $section['layout']); ?></p>
                    <div style="display:flex; gap:12px;">
                        <?php foreach ($section['columns'] as $column) { ?>
                            <div style="flex:1; border:1px dashed #bbb; padding:10px;">
                                <p><strong><?php html($column['title']); ?></strong></p>
                                <?php foreach ($column['nodes'] as $node) { ?>
                                    <div style="border:1px solid #ddd; padding:8px; margin-bottom:8px;">
                                        <div><small><?php html($node_type_titles[$node['type']] ?? $node['type']); ?></small></div>
                                        <div><?php html($node['label']); ?></div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div style="width:24%;">
            <h4>Панель настроек</h4>
            <p>Системных виджетов на странице: <?php echo count($screen['widget_nodes']); ?></p>
            <ul>
                <li>название и заметки элемента</li>
                <li>источник данных</li>
                <li>настройки системного виджета</li>
                <li>показ на разных устройствах</li>
                <li>параметры секции и колонок</li>
            </ul>
        </div>
    </div>
</div>