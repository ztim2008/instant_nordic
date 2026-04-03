<?php

$this->setPageTitle('Canvas: ' . $page['title']);
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
    <p>Ключ: <code><?php html($page['key']); ?></code> | Режим: <code><?php html($page['mode']); ?></code> | Статус: <?php html($page['status']); ?></p>
    <p>API: <code><?php html($screen['api']['widgets_catalog_url']); ?></code> | <code><?php html($screen['api']['widget_options_url']); ?></code> | <code><?php html($screen['api']['canvas_save_url']); ?></code></p>

    <p><strong>Devices:</strong>
        <?php foreach ($screen['devices'] as $device) { ?>
            <span style="margin-right: 8px;"><?php html($device); ?></span>
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
            <h4>Canvas</h4>
            <?php foreach ($screen['sections'] as $section) { ?>
                <div style="border:1px solid #ccc; padding:12px; margin-bottom:12px;">
                    <p><strong><?php html($section['title']); ?></strong> | <code><?php html($section['layout']); ?></code></p>
                    <div style="display:flex; gap:12px;">
                        <?php foreach ($section['columns'] as $column) { ?>
                            <div style="flex:1; border:1px dashed #bbb; padding:10px;">
                                <p><strong><?php html($column['title']); ?></strong></p>
                                <?php foreach ($column['nodes'] as $node) { ?>
                                    <div style="border:1px solid #ddd; padding:8px; margin-bottom:8px;">
                                        <div><small><?php html($node['type']); ?></small></div>
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
            <h4>Inspector</h4>
            <p>Системные widget-узлы: <?php echo count($screen['widget_nodes']); ?></p>
            <ul>
                <li>props блока</li>
                <li>dynamic source</li>
                <li>widget options form</li>
                <li>visibility по устройствам</li>
                <li>layout overrides</li>
            </ul>
        </div>
    </div>
</div>