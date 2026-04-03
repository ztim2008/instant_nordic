<?php

$this->setPageTitle('Canvas: ' . $page['title']);
$this->addBreadcrumb('Нордик');
$this->addBreadcrumb('Страницы', href_to('admin', 'controllers', ['edit', 'landingbuilder', 'pages']));
$this->addBreadcrumb($page['title']);
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'save',
    'title' => 'Сохранить',
    'href'  => '#',
    'icon'  => 'save'
]);

$this->addToolButton([
    'class' => 'view',
    'title' => 'Предпросмотр',
    'href'  => '#',
    'icon'  => 'eye'
]);

?>
<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h3 class="h5 mb-2"><?php html($page['title']); ?></h3>
            <div class="text-muted">Ключ: <code><?php html($page['key']); ?></code> | Режим: <code><?php html($page['mode']); ?></code> | Статус: <?php html($page['status']); ?></div>
        </div>
        <div class="btn-group mt-3 mt-md-0" role="group" aria-label="Devices">
            <?php foreach ($screen['devices'] as $device) { ?>
                <button type="button" class="btn btn-outline-secondary<?php if ($device === 'desktop') { ?> active<?php } ?>"><?php html($device); ?></button>
            <?php } ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-header">Левая панель</div>
            <div class="card-body">
                <ul class="nav nav-pills flex-column mb-3">
                    <?php foreach ($screen['left_tabs'] as $index => $tab) { ?>
                        <li class="nav-item mb-2">
                            <span class="nav-link<?php if ($index === 0) { ?> active<?php } ?>"><?php html($tab); ?></span>
                        </li>
                    <?php } ?>
                </ul>
                <p class="text-muted mb-0">Здесь будет библиотека секций, блоков и стандартных widgets InstantCMS.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Canvas</div>
            <div class="card-body">
                <?php foreach ($screen['sections'] as $section) { ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong><?php html($section['title']); ?></strong>
                            <span class="badge badge-light"><?php html($section['layout']); ?></span>
                        </div>
                        <div class="row">
                            <?php foreach ($section['columns'] as $column) { ?>
                                <div class="col-md-<?php echo max(4, (int) floor(12 / max(1, count($section['columns'])))); ?> mb-3">
                                    <div class="border rounded p-2 h-100 bg-light">
                                        <div class="small font-weight-bold mb-2"><?php html($column['title']); ?></div>
                                        <?php foreach ($column['nodes'] as $node) { ?>
                                            <div class="border rounded bg-white p-2 mb-2">
                                                <div class="small text-muted text-uppercase mb-1"><?php html($node['type']); ?></div>
                                                <div><?php html($node['label']); ?></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-header">Inspector</div>
            <div class="card-body">
                <p class="mb-2"><strong>Что должно жить здесь:</strong></p>
                <ul class="mb-0 pl-3">
                    <li>props блока</li>
                    <li>dynamic source</li>
                    <li>widget options form</li>
                    <li>visibility desktop/tablet/mobile</li>
                    <li>layout и responsive overrides</li>
                </ul>
            </div>
        </div>
    </div>
</div>