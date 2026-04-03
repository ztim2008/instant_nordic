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

$canvas_state = [
    'page' => [
        'id'         => $page['id'],
        'key'        => $page['key'],
        'title'      => $page['title'],
        'status'     => $page['status'],
        'mode'       => $page['mode'],
        'updated_at' => $page['updated_at'],
        'template'   => !empty($page['template']) ? $page['template'] : 'nordic'
    ],
    'schema' => $page['schema'],
    'screen' => $screen
];

?>
<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h3 class="h5 mb-2"><?php html($page['title']); ?></h3>
            <div class="text-muted"><span id="lb-page-meta">Ключ: <code><?php html($page['key']); ?></code> | Режим: <code><?php html($page['mode']); ?></code> | Статус: <?php html($page['status']); ?></span></div>
            <div class="small text-muted mt-2">Последнее обновление: <span id="lb-updated-at"><?php html($page['updated_at']); ?></span></div>
        </div>
        <div class="d-flex flex-column align-items-md-end mt-3 mt-md-0">
            <div class="btn-group mb-2" role="group" aria-label="Canvas actions">
                <button type="button" class="btn btn-primary" id="lb-save-canvas">Сохранить canvas</button>
                <button type="button" class="btn btn-outline-secondary" id="lb-add-section">Добавить секцию</button>
            </div>
            <div class="btn-group" role="group" aria-label="Devices">
            <?php foreach ($screen['devices'] as $device) { ?>
                <button type="button" class="btn btn-outline-secondary lb-device-toggle<?php if ($device === 'desktop') { ?> active<?php } ?>" data-device="<?php html($device); ?>"><?php html($device); ?></button>
            <?php } ?>
            </div>
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
                            <button type="button" class="nav-link text-left w-100 border-0 lb-library-tab<?php if ($index === 0) { ?> active<?php } ?>" data-tab="<?php echo $index === 0 ? 'blocks' : 'widgets'; ?>"><?php html($tab); ?></button>
                        </li>
                    <?php } ?>
                </ul>
                <div id="lb-blocks-library">
                    <div class="small text-muted mb-2">Быстрые block presets</div>
                    <div class="list-group list-group-flush" id="lb-block-list"></div>
                </div>
                <div id="lb-widgets-library" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">Каталог системных widgets</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="lb-reload-widgets">Обновить</button>
                    </div>
                    <div id="lb-widget-list" class="small"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Canvas</span>
                <span class="small text-muted" id="lb-canvas-status"><?php if ($screen['schema_installed']) { ?>SQL schema active<?php } else { ?>Fallback mode<?php } ?></span>
            </div>
            <div class="card-body" id="lb-canvas-root"></div>
            <div class="card-footer bg-white border-top-0">
                <div class="small text-muted">Выдели колонку и затем вставь block или system widget из левой библиотеки.</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-header">Inspector</div>
            <div class="card-body">
                <p class="mb-2"><strong>Системные widget-узлы:</strong> <span id="lb-widget-count"><?php echo count($screen['widget_nodes']); ?></span></p>
                <div class="mb-3">
                    <label class="small text-muted d-block mb-1">Комментарий версии</label>
                    <input type="text" class="form-control form-control-sm" id="lb-version-note" placeholder="Например: перестроил hero и sidebar">
                </div>
                <div class="mb-3">
                    <div class="small text-muted mb-1">Выделение</div>
                    <div id="lb-selection-summary" class="small">Ничего не выбрано</div>
                </div>
                <div id="lb-selection-controls" class="mb-3"></div>
                <div class="mb-3">
                    <div class="small text-muted mb-2">Настройки system widget</div>
                    <div id="lb-widget-form" class="border rounded p-2 bg-light small">Выбери widget-узел, чтобы загрузить штатную форму InstantCMS.</div>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">История версий</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="lb-refresh-versions">Обновить</button>
                    </div>
                    <div id="lb-versions-list" class="small"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    (function () {
        const state = <?php echo json_encode($canvas_state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const blockPresets = [
            {label: 'core.hero-heading'},
            {label: 'core.hero-actions'},
            {label: 'core.cards-grid'},
            {label: 'core.feature-list'},
            {label: 'ads.category-header'},
            {label: 'profile.cover-hero'}
        ];

        state.widgetsCatalog = {};
        state.activeDevice = state.screen.devices[0] || 'desktop';
        state.selection = null;

        const canvasRoot = document.getElementById('lb-canvas-root');
        const blockList = document.getElementById('lb-block-list');
        const widgetList = document.getElementById('lb-widget-list');
        const widgetForm = document.getElementById('lb-widget-form');
        const versionsList = document.getElementById('lb-versions-list');
        const selectionSummary = document.getElementById('lb-selection-summary');
        const selectionControls = document.getElementById('lb-selection-controls');
        const widgetCount = document.getElementById('lb-widget-count');
        const updatedAt = document.getElementById('lb-updated-at');
        const versionNote = document.getElementById('lb-version-note');
        const pageMeta = document.getElementById('lb-page-meta');
        const canvasStatus = document.getElementById('lb-canvas-status');

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function sectionColumnClass(count) {
            if (count <= 1) {
                return 'col-12';
            }
            if (count === 2) {
                return 'col-md-6';
            }
            return 'col-md-4';
        }

        function ensureSelection() {
            if (state.selection) {
                return true;
            }

            if (!state.schema.sections.length) {
                return false;
            }

            state.selection = {type: 'section', sectionIndex: 0};
            return true;
        }

        function getSelectedNode() {
            if (!state.selection || state.selection.type !== 'node') {
                return null;
            }

            const section = state.schema.sections[state.selection.sectionIndex];
            const column = section && section.columns[state.selection.columnIndex];
            return column && column.nodes[state.selection.nodeIndex] ? column.nodes[state.selection.nodeIndex] : null;
        }

        function getSelectedColumn() {
            if (!state.selection) {
                return null;
            }

            if (state.selection.type === 'column' || state.selection.type === 'node') {
                const section = state.schema.sections[state.selection.sectionIndex];
                return section && section.columns[state.selection.columnIndex] ? section.columns[state.selection.columnIndex] : null;
            }

            return null;
        }

        function uid(prefix) {
            return prefix + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 7);
        }

        function syncSelectedWidgetFormIntoState() {
            const node = getSelectedNode();
            if (!node || node.type !== 'system_widget') {
                return;
            }

            const form = widgetForm.querySelector('form');
            if (!form) {
                return;
            }

            const formData = new FormData(form);
            const options = {};

            form.querySelectorAll('input, select, textarea').forEach(function (element) {
                if (!element.name) {
                    return;
                }

                if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
                    return;
                }

                const values = formData.getAll(element.name);
                options[element.name] = values.length > 1 ? values : values[0];
            });

            node.options = options;
        }

        function renderBlockLibrary() {
            blockList.innerHTML = blockPresets.map(function (block, index) {
                return '' +
                    '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-role="insert-block" data-block-index="' + index + '">' +
                        '<span>' + escapeHtml(block.label) + '</span>' +
                        '<span class="badge badge-light">Вставить</span>' +
                    '</button>';
            }).join('');
        }

        function renderWidgetLibrary() {
            const groups = Object.keys(state.widgetsCatalog);
            if (!groups.length) {
                widgetList.innerHTML = '<div class="text-muted">Каталог widgets пока пуст или ещё не загружен.</div>';
                return;
            }

            widgetList.innerHTML = groups.map(function (group) {
                const items = state.widgetsCatalog[group] || [];
                return '' +
                    '<div class="mb-3">' +
                        '<div class="font-weight-bold text-uppercase small mb-2">' + escapeHtml(group) + '</div>' +
                        items.map(function (widget) {
                            return '' +
                                '<button type="button" class="list-group-item list-group-item-action mb-1 border rounded" data-role="insert-widget" data-widget-id="' + widget.id + '">' +
                                    '<div class="font-weight-bold">' + escapeHtml(widget.title) + '</div>' +
                                    '<div class="text-muted small">' + escapeHtml((widget.controller || 'core') + '.' + widget.name) + '</div>' +
                                '</button>';
                        }).join('') +
                    '</div>';
            }).join('');
        }

        function renderCanvas() {
            if (!state.schema.sections.length) {
                canvasRoot.innerHTML = '<div class="alert alert-light border">Секция ещё не добавлена. Нажми «Добавить секцию».</div>';
                renderInspector();
                return;
            }

            canvasRoot.innerHTML = state.schema.sections.map(function (section, sectionIndex) {
                return '' +
                    '<div class="border rounded p-3 mb-3 lb-section' + (state.selection && state.selection.sectionIndex === sectionIndex ? ' border-primary' : '') + '" data-role="section" data-section-index="' + sectionIndex + '">' +
                        '<div class="d-flex justify-content-between align-items-center mb-3">' +
                            '<div>' +
                                '<strong>' + escapeHtml(section.title) + '</strong>' +
                                '<div class="small text-muted">Layout: ' + escapeHtml(section.layout) + '</div>' +
                            '</div>' +
                            '<div class="btn-group btn-group-sm">' +
                                '<button type="button" class="btn btn-outline-secondary" data-action="rename-section" data-section-index="' + sectionIndex + '">Переименовать</button>' +
                                '<button type="button" class="btn btn-outline-secondary" data-action="change-layout" data-section-index="' + sectionIndex + '">Layout</button>' +
                                '<button type="button" class="btn btn-outline-danger" data-action="delete-section" data-section-index="' + sectionIndex + '">Удалить</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="row">' +
                            section.columns.map(function (column, columnIndex) {
                                return '' +
                                    '<div class="' + sectionColumnClass(section.columns.length) + ' mb-3">' +
                                        '<div class="border rounded p-2 h-100 bg-light lb-column' + (state.selection && state.selection.sectionIndex === sectionIndex && state.selection.columnIndex === columnIndex ? ' border-primary' : '') + '" data-role="column" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '">' +
                                            '<div class="d-flex justify-content-between align-items-center mb-2">' +
                                                '<div class="small font-weight-bold">' + escapeHtml(column.title) + '</div>' +
                                                '<button type="button" class="btn btn-link btn-sm p-0" data-action="rename-column" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '">имя</button>' +
                                            '</div>' +
                                            '<div class="small text-muted mb-2">Кликни для выбора колонки, затем вставляй элементы слева.</div>' +
                                            '<div>' +
                                                column.nodes.map(function (node, nodeIndex) {
                                                    return '' +
                                                        '<div class="border rounded bg-white p-2 mb-2 lb-node' + (state.selection && state.selection.type === 'node' && state.selection.sectionIndex === sectionIndex && state.selection.columnIndex === columnIndex && state.selection.nodeIndex === nodeIndex ? ' border-primary' : '') + '" data-role="node" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '" data-node-index="' + nodeIndex + '">' +
                                                            '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                                                '<div class="small text-muted text-uppercase">' + escapeHtml(node.type) + '</div>' +
                                                                '<button type="button" class="btn btn-link btn-sm text-danger p-0" data-action="delete-node" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '" data-node-index="' + nodeIndex + '">удалить</button>' +
                                                            '</div>' +
                                                            '<div>' + escapeHtml(node.label) + '</div>' +
                                                        '</div>';
                                                }).join('') +
                                            '</div>' +
                                        '</div>' +
                                    '</div>';
                            }).join('') +
                        '</div>' +
                    '</div>';
            }).join('');

            widgetCount.textContent = String(countWidgetNodes());
            renderInspector();
        }

        function renderInspector() {
            const selection = state.selection;

            if (!selection) {
                selectionSummary.textContent = 'Ничего не выбрано';
                selectionControls.innerHTML = '';
                widgetForm.innerHTML = 'Выбери widget-узел, чтобы загрузить штатную форму InstantCMS.';
                return;
            }

            if (selection.type === 'section') {
                const section = state.schema.sections[selection.sectionIndex];
                selectionSummary.textContent = 'Секция: ' + section.title;
                selectionControls.innerHTML = '' +
                    '<div class="small text-muted mb-1">Быстрые действия секции</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary mr-2" data-action="rename-section" data-section-index="' + selection.sectionIndex + '">Переименовать</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary" data-action="change-layout" data-section-index="' + selection.sectionIndex + '">Сменить layout</button>';
                widgetForm.innerHTML = 'Выбери widget-узел, чтобы загрузить штатную форму InstantCMS.';
                return;
            }

            if (selection.type === 'column') {
                const column = getSelectedColumn();
                selectionSummary.textContent = column ? 'Колонка: ' + column.title : 'Колонка';
                selectionControls.innerHTML = '' +
                    '<div class="small text-muted mb-1">В эту колонку можно вставлять block и system widget из левой панели.</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-secondary" data-action="rename-column" data-section-index="' + selection.sectionIndex + '" data-column-index="' + selection.columnIndex + '">Переименовать колонку</button>';
                widgetForm.innerHTML = 'Выбери widget-узел, чтобы загрузить штатную форму InstantCMS.';
                return;
            }

            const node = getSelectedNode();
            if (!node) {
                selectionSummary.textContent = 'Выбранный элемент не найден';
                selectionControls.innerHTML = '';
                widgetForm.innerHTML = 'Выбери widget-узел, чтобы загрузить штатную форму InstantCMS.';
                return;
            }

            selectionSummary.innerHTML = '<strong>' + escapeHtml(node.label) + '</strong><br><span class="text-muted">Тип: ' + escapeHtml(node.type) + '</span>';
            selectionControls.innerHTML = '' +
                '<div class="form-group mb-2">' +
                    '<label class="small text-muted d-block mb-1">Label</label>' +
                    '<input type="text" class="form-control form-control-sm" id="lb-node-label" value="' + escapeHtml(node.label) + '">' +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="lb-apply-node-label">Применить label</button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-node" data-section-index="' + selection.sectionIndex + '" data-column-index="' + selection.columnIndex + '" data-node-index="' + selection.nodeIndex + '">Удалить node</button>';

            if (node.type === 'system_widget' && node.widget_id) {
                loadWidgetOptions(node);
            } else if (node.type === 'system_widget') {
                widgetForm.innerHTML = 'У этого widget-узла пока нет widget_id. Добавь его из системного каталога слева.';
            } else {
                widgetForm.innerHTML = 'Для builder block здесь будет props editor. Пока доступно быстрое редактирование label.';
            }
        }

        function countWidgetNodes() {
            let count = 0;
            state.schema.sections.forEach(function (section) {
                section.columns.forEach(function (column) {
                    column.nodes.forEach(function (node) {
                        if (node.type === 'system_widget') {
                            count += 1;
                        }
                    });
                });
            });
            return count;
        }

        async function loadWidgetCatalog() {
            widgetList.innerHTML = '<div class="text-muted">Загрузка каталога widgets...</div>';

            const response = await fetch(state.screen.api.widgets_catalog_url, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                widgetList.innerHTML = '<div class="text-danger">Не удалось загрузить каталог widgets.</div>';
                return;
            }

            state.widgetsCatalog = result.widgets || {};
            renderWidgetLibrary();
        }

        async function loadVersions() {
            const response = await fetch(state.screen.api.versions_url + '?page_key=' + encodeURIComponent(state.page.key), {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (!result.error) {
                state.screen.versions = result.versions || [];
            }

            renderVersions();
        }

        function renderVersions() {
            const versions = state.screen.versions || [];
            if (!versions.length) {
                versionsList.innerHTML = '<div class="text-muted">Версий пока нет. Первая появится после сохранения canvas.</div>';
                return;
            }

            versionsList.innerHTML = versions.map(function (version) {
                return '' +
                    '<div class="border rounded p-2 mb-2">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<strong>#' + version.id + '</strong>' +
                            '<button type="button" class="btn btn-sm btn-outline-secondary" data-action="restore-version" data-version-id="' + version.id + '">Восстановить</button>' +
                        '</div>' +
                        '<div class="small text-muted mt-1">' + escapeHtml(version.created_at) + '</div>' +
                        '<div class="small mt-1">' + escapeHtml(version.version_note || 'Без комментария') + '</div>' +
                    '</div>';
            }).join('');
        }

        async function loadWidgetOptions(node) {
            widgetForm.innerHTML = 'Загрузка формы widget...';

            const body = new URLSearchParams();
            body.set('widget_id', node.widget_id);
            body.set('template', state.page.template || 'nordic');
            body.set('options', JSON.stringify(node.options || {}));

            const response = await fetch(state.screen.api.widget_options_url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString(),
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                widgetForm.innerHTML = '<div class="text-danger">Не удалось загрузить widget form.</div>';
                return;
            }

            widgetForm.innerHTML = result.html;
        }

        function setSelection(selection) {
            syncSelectedWidgetFormIntoState();
            state.selection = selection;
            renderCanvas();
        }

        function addSection() {
            const title = window.prompt('Название секции', 'Новая секция');
            if (!title) {
                return;
            }

            const layout = window.prompt('Layout: 1col / 2col_equal / 2col_sidebar_left / 2col_sidebar_right / 3col_equal', '2col_equal') || '2col_equal';
            const columnsCount = layout === '1col' ? 1 : (layout.indexOf('3col') === 0 ? 3 : 2);
            const sectionUid = uid('section');

            const columns = [];
            for (let index = 0; index < columnsCount; index += 1) {
                columns.push({
                    uid: sectionUid + '-column-' + (index + 1),
                    title: 'Колонка ' + (index + 1),
                    nodes: []
                });
            }

            state.schema.sections.push({
                uid: sectionUid,
                title: title,
                layout: layout,
                columns: columns
            });

            setSelection({type: 'section', sectionIndex: state.schema.sections.length - 1});
        }

        function insertBlock(blockIndex) {
            const column = getSelectedColumn();
            if (!column) {
                window.alert('Сначала выбери колонку на canvas.');
                return;
            }

            const block = blockPresets[blockIndex];
            if (!block) {
                return;
            }

            column.nodes.push({
                uid: uid('node'),
                type: 'block',
                label: block.label
            });

            setSelection({
                type: 'node',
                sectionIndex: state.selection.sectionIndex,
                columnIndex: state.selection.columnIndex,
                nodeIndex: column.nodes.length - 1
            });
        }

        function findWidgetById(widgetId) {
            const groups = Object.keys(state.widgetsCatalog);
            for (let index = 0; index < groups.length; index += 1) {
                const items = state.widgetsCatalog[groups[index]] || [];
                for (let inner = 0; inner < items.length; inner += 1) {
                    if (String(items[inner].id) === String(widgetId)) {
                        return items[inner];
                    }
                }
            }
            return null;
        }

        function insertWidget(widgetId) {
            const column = getSelectedColumn();
            if (!column) {
                window.alert('Сначала выбери колонку на canvas.');
                return;
            }

            const widget = findWidgetById(widgetId);
            if (!widget) {
                window.alert('Widget не найден в каталоге.');
                return;
            }

            column.nodes.push({
                uid: uid('node'),
                type: 'system_widget',
                label: (widget.controller || 'core') + '.' + widget.name,
                widget_id: widget.id,
                widget_name: widget.name,
                widget_controller: widget.controller,
                options: {},
                device_visibility: {}
            });

            setSelection({
                type: 'node',
                sectionIndex: state.selection.sectionIndex,
                columnIndex: state.selection.columnIndex,
                nodeIndex: column.nodes.length - 1
            });
        }

        async function saveCanvas() {
            syncSelectedWidgetFormIntoState();

            const body = new URLSearchParams();
            body.set('page_key', state.page.key);
            body.set('schema', JSON.stringify(state.schema));
            body.set('version_note', versionNote.value || 'Сохранение canvas');

            const response = await fetch(state.screen.api.canvas_save_url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString(),
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                window.alert(result.message || 'Canvas save failed');
                return;
            }

            state.page = Object.assign({}, state.page, result.page || {});
            updatedAt.textContent = state.page.updated_at || '';
            pageMeta.innerHTML = 'Ключ: <code>' + escapeHtml(state.page.key) + '</code> | Режим: <code>' + escapeHtml(state.page.mode) + '</code> | Статус: ' + escapeHtml(state.page.status);
            canvasStatus.textContent = 'Canvas сохранен';
            versionNote.value = '';
            await loadVersions();
            renderCanvas();
        }

        async function restoreVersion(versionId) {
            if (!window.confirm('Восстановить выбранную версию canvas?')) {
                return;
            }

            const body = new URLSearchParams();
            body.set('version_id', versionId);

            const response = await fetch(state.screen.api.version_restore_url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: body.toString(),
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                window.alert(result.message || 'Не удалось восстановить версию');
                return;
            }

            state.page = Object.assign({}, state.page, result.page || {});
            state.schema = result.schema || {sections: []};
            state.screen.versions = result.versions || [];
            state.selection = null;
            updatedAt.textContent = state.page.updated_at || '';
            renderCanvas();
            renderVersions();
        }

        document.querySelectorAll('.lb-library-tab').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelectorAll('.lb-library-tab').forEach(function (item) {
                    item.classList.remove('active');
                });
                button.classList.add('active');
                document.getElementById('lb-blocks-library').classList.toggle('d-none', button.dataset.tab !== 'blocks');
                document.getElementById('lb-widgets-library').classList.toggle('d-none', button.dataset.tab !== 'widgets');
            });
        });

        document.querySelectorAll('.lb-device-toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                document.querySelectorAll('.lb-device-toggle').forEach(function (item) {
                    item.classList.remove('active');
                });
                button.classList.add('active');
                state.activeDevice = button.dataset.device;
            });
        });

        document.getElementById('lb-add-section').addEventListener('click', addSection);
        document.getElementById('lb-save-canvas').addEventListener('click', function () {
            saveCanvas().catch(function (error) {
                console.error(error);
                window.alert('Ошибка сохранения canvas');
            });
        });
        document.getElementById('lb-reload-widgets').addEventListener('click', function () {
            loadWidgetCatalog().catch(function (error) {
                console.error(error);
                widgetList.innerHTML = '<div class="text-danger">Ошибка загрузки widgets.</div>';
            });
        });
        document.getElementById('lb-refresh-versions').addEventListener('click', function () {
            loadVersions().catch(function (error) {
                console.error(error);
            });
        });

        function handleActionTarget(actionTarget) {
            if (!actionTarget) {
                return false;
            }

            if (actionTarget.id === 'lb-apply-node-label') {
                const input = document.getElementById('lb-node-label');
                const node = getSelectedNode();
                if (input && node) {
                    node.label = input.value.trim() || node.label;
                    renderCanvas();
                }
                return true;
            }

            const sectionIndex = Number(actionTarget.dataset.sectionIndex);
            const columnIndex = Number(actionTarget.dataset.columnIndex);
            const nodeIndex = Number(actionTarget.dataset.nodeIndex);
            const action = actionTarget.dataset.action;

            if (action === 'rename-section') {
                const section = state.schema.sections[sectionIndex];
                const title = window.prompt('Название секции', section.title);
                if (title) {
                    section.title = title;
                    renderCanvas();
                }
                return true;
            }

            if (action === 'change-layout') {
                const section = state.schema.sections[sectionIndex];
                const layout = window.prompt('Layout секции', section.layout);
                if (layout) {
                    section.layout = layout;
                    renderCanvas();
                }
                return true;
            }

            if (action === 'rename-column') {
                const column = state.schema.sections[sectionIndex].columns[columnIndex];
                const title = window.prompt('Название колонки', column.title);
                if (title) {
                    column.title = title;
                    renderCanvas();
                }
                return true;
            }

            if (action === 'delete-section') {
                if (window.confirm('Удалить секцию?')) {
                    state.schema.sections.splice(sectionIndex, 1);
                    state.selection = null;
                    renderCanvas();
                }
                return true;
            }

            if (action === 'delete-node') {
                if (window.confirm('Удалить node?')) {
                    state.schema.sections[sectionIndex].columns[columnIndex].nodes.splice(nodeIndex, 1);
                    state.selection = null;
                    renderCanvas();
                }
                return true;
            }

            return false;
        }

        selectionControls.addEventListener('click', function (event) {
            const actionTarget = event.target.closest('[data-action], #lb-apply-node-label');
            if (handleActionTarget(actionTarget)) {
                event.preventDefault();
            }
        });

        canvasRoot.addEventListener('click', function (event) {
            const actionTarget = event.target.closest('[data-action]');
            if (handleActionTarget(actionTarget)) {
                event.preventDefault();
                return;
            }

            const nodeTarget = event.target.closest('[data-role="node"]');
            if (nodeTarget) {
                setSelection({
                    type: 'node',
                    sectionIndex: Number(nodeTarget.dataset.sectionIndex),
                    columnIndex: Number(nodeTarget.dataset.columnIndex),
                    nodeIndex: Number(nodeTarget.dataset.nodeIndex)
                });
                return;
            }

            const columnTarget = event.target.closest('[data-role="column"]');
            if (columnTarget) {
                setSelection({
                    type: 'column',
                    sectionIndex: Number(columnTarget.dataset.sectionIndex),
                    columnIndex: Number(columnTarget.dataset.columnIndex)
                });
                return;
            }

            const sectionTarget = event.target.closest('[data-role="section"]');
            if (sectionTarget) {
                setSelection({
                    type: 'section',
                    sectionIndex: Number(sectionTarget.dataset.sectionIndex)
                });
            }
        });

        widgetList.addEventListener('click', function (event) {
            const button = event.target.closest('[data-role="insert-widget"]');
            if (!button) {
                return;
            }

            insertWidget(button.dataset.widgetId);
        });

        blockList.addEventListener('click', function (event) {
            const button = event.target.closest('[data-role="insert-block"]');
            if (!button) {
                return;
            }

            insertBlock(Number(button.dataset.blockIndex));
        });

        versionsList.addEventListener('click', function (event) {
            const button = event.target.closest('[data-action="restore-version"]');
            if (!button) {
                return;
            }

            restoreVersion(button.dataset.versionId).catch(function (error) {
                console.error(error);
                window.alert('Ошибка восстановления версии');
            });
        });

        renderBlockLibrary();
        ensureSelection();
        renderCanvas();
        renderVersions();
        loadWidgetCatalog().catch(function (error) {
            console.error(error);
            widgetList.innerHTML = '<div class="text-danger">Ошибка загрузки widgets.</div>';
        });
    })();
</script>
<?php $this->addBottom(ob_get_clean()); ?>