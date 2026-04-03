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

$page_mode_title = $page_mode_titles[$page['mode']] ?? $page['mode'];
$page_status_title = $page_status_titles[$page['status']] ?? $page['status'];

$this->setPageTitle('Редактор страницы: ' . $page['title']);
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
<style>
    .lb-section[draggable="true"],
    .lb-node[draggable="true"] {
        cursor: move;
    }

    .lb-dragging {
        opacity: 0.45;
    }

    .lb-drop-target {
        box-shadow: 0 0 0 2px rgba(23, 162, 184, 0.45) inset;
    }

    .lb-muted-device {
        opacity: 0.55;
    }

    .lb-help {
        display: inline-flex;
        align-items: center;
        margin-left: 6px;
        color: #6c757d;
        cursor: help;
    }

    .lb-help i {
        font-size: 13px;
    }

    .lb-library-caption {
        line-height: 1.25;
    }
</style>
<div class="card mb-4">
    <div class="card-body d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h3 class="h5 mb-2"><?php html($page['title']); ?></h3>
            <div class="text-muted"><span id="lb-page-meta">Ключ страницы: <code><?php html($page['key']); ?></code> | Режим: <?php html($page_mode_title); ?> | Статус: <?php html($page_status_title); ?></span></div>
            <div class="small text-muted mt-2">Последнее обновление: <span id="lb-updated-at"><?php html($page['updated_at']); ?></span></div>
        </div>
        <div class="d-flex flex-column align-items-md-end mt-3 mt-md-0">
            <div class="btn-group mb-2" role="group" aria-label="Действия редактора">
                <button type="button" class="btn btn-primary" id="lb-save-canvas">Сохранить изменения</button>
                <button type="button" class="btn btn-outline-secondary" id="lb-add-section">Добавить секцию</button>
            </div>
            <div class="btn-group" role="group" aria-label="Устройства">
            <?php foreach ($screen['devices'] as $device) { ?>
                <button type="button" class="btn btn-outline-secondary lb-device-toggle<?php if ($device === 'desktop') { ?> active<?php } ?>" data-device="<?php html($device); ?>"><?php html($device_titles[$device] ?? $device); ?></button>
            <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-header">Библиотека элементов</div>
            <div class="card-body">
                <ul class="nav nav-pills flex-column mb-3">
                    <?php foreach ($screen['left_tabs'] as $index => $tab) { ?>
                        <li class="nav-item mb-2">
                            <button type="button" class="nav-link text-left w-100 border-0 lb-library-tab<?php if ($index === 0) { ?> active<?php } ?>" data-tab="<?php echo $index === 0 ? 'blocks' : 'widgets'; ?>"><?php html($tab); ?></button>
                        </li>
                    <?php } ?>
                </ul>
                <div id="lb-blocks-library">
                    <div class="small text-muted mb-2">Готовые блоки для быстрого старта</div>
                    <div class="list-group list-group-flush" id="lb-block-list"></div>
                </div>
                <div id="lb-widgets-library" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="small text-muted">Системные виджеты InstantCMS</div>
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
                <span>Макет страницы</span>
                <span class="small text-muted" id="lb-canvas-status"><?php if ($screen['schema_installed']) { ?>Работа с базой данных<?php } else { ?>Временный режим без базы<?php } ?></span>
            </div>
            <div class="card-body" id="lb-canvas-root"></div>
            <div class="card-footer bg-white border-top-0">
                <div class="small text-muted">Сначала выберите колонку, затем добавляйте блоки или виджеты из библиотеки слева. Секции и элементы можно перетаскивать мышью.</div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-header">Панель настроек</div>
            <div class="card-body">
                <p class="mb-2"><strong>Виджетов на странице:</strong> <span id="lb-widget-count"><?php echo count($screen['widget_nodes']); ?></span></p>
                <div class="mb-3">
                    <label class="small text-muted d-block mb-1">Комментарий версии</label>
                    <input type="text" class="form-control form-control-sm" id="lb-version-note" placeholder="Например: перестроил первый экран и боковую колонку">
                </div>
                <div class="mb-3">
                    <div class="small text-muted mb-1">Выделение</div>
                    <div id="lb-selection-summary" class="small">Ничего не выбрано</div>
                </div>
                <div id="lb-selection-controls" class="mb-3"></div>
                <div class="mb-3">
                    <div class="small text-muted mb-2">Настройки выбранного виджета</div>
                    <div id="lb-widget-form" class="border rounded p-2 bg-light small">Выберите системный виджет на макете, чтобы открыть его штатные настройки.</div>
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
        const deviceTitles = <?php echo json_encode($device_titles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const pageModeTitles = <?php echo json_encode($page_mode_titles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const pageStatusTitles = <?php echo json_encode($page_status_titles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const blockPresets = [
            {
                key: 'core.hero-heading',
                title: 'Главный экран с заголовком',
                description: 'Крупный первый блок страницы с основным сообщением и подзаголовком.'
            },
            {
                key: 'core.hero-actions',
                title: 'Главный экран с кнопками',
                description: 'Первый экран с призывом к действию и заметными кнопками.'
            },
            {
                key: 'core.cards-grid',
                title: 'Сетка карточек',
                description: 'Подходит для преимуществ, услуг, тарифов или подборок.'
            },
            {
                key: 'core.feature-list',
                title: 'Список преимуществ',
                description: 'Короткий блок с причинами выбрать предложение.'
            },
            {
                key: 'ads.category-header',
                title: 'Шапка категории объявлений',
                description: 'Верхняя часть страницы категории с акцентом на заголовок и фильтры.'
            },
            {
                key: 'profile.cover-hero',
                title: 'Обложка профиля',
                description: 'Широкий блок для профиля пользователя или компании.'
            }
        ];
        const blockPresetMap = blockPresets.reduce(function (map, block) {
            map[block.key] = block;
            return map;
        }, {});
        const layoutOptions = [
            {value: '1col', title: 'Одна колонка', hint: 'Один широкий столбец на всю ширину секции.'},
            {value: '2col_equal', title: 'Две равные колонки', hint: 'Подходит для текста рядом с изображением или формы рядом с описанием.'},
            {value: '2col_sidebar_left', title: 'Узкая колонка слева', hint: 'Слева узкий вспомогательный блок, справа основное содержимое.'},
            {value: '2col_sidebar_right', title: 'Узкая колонка справа', hint: 'Справа узкий вспомогательный блок, слева основное содержимое.'},
            {value: '3col_equal', title: 'Три равные колонки', hint: 'Подходит для карточек, этапов или трёх преимуществ.'}
        ];
        const columnWidthOptions = [
            {value: 'auto', title: 'Авто'},
            {value: '12', title: 'Во всю ширину'},
            {value: '8', title: 'Широкая'},
            {value: '6', title: 'Половина'},
            {value: '4', title: 'Узкая'},
            {value: '3', title: 'Очень узкая'}
        ];
        const alignOptions = [
            {value: 'stretch', title: 'Растянуть'},
            {value: 'start', title: 'По верхнему краю'},
            {value: 'center', title: 'По центру'},
            {value: 'end', title: 'По нижнему краю'}
        ];

        state.widgetsCatalog = {};
        state.activeDevice = state.screen.devices[0] || 'desktop';
        state.selection = null;
        state.drag = null;
        state.schema = normalizeSchema(state.schema || {sections: []});

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

        function defaultVisibility() {
            const visibility = {desktop: true, mobile: true};
            if (state.screen.devices.indexOf('tablet') !== -1) {
                visibility.tablet = true;
            }
            return visibility;
        }

        function defaultColumnWidth() {
            const width = {desktop: 'auto', mobile: 'auto'};
            if (state.screen.devices.indexOf('tablet') !== -1) {
                width.tablet = 'auto';
            }
            return width;
        }

        function normalizeSchema(schema) {
            const next = Object.assign({sections: []}, schema || {});
            next.sections = Array.isArray(next.sections) ? next.sections.map(function (section, sectionIndex) {
                return normalizeSection(section, sectionIndex);
            }) : [];
            return next;
        }

        function normalizeSection(section, sectionIndex) {
            const next = Object.assign({}, section || {});
            next.uid = next.uid || uid('section');
            next.title = next.title || ('Секция ' + ((sectionIndex || 0) + 1));
            next.layout = next.layout || '1col';
            next.visibility = Object.assign(defaultVisibility(), next.visibility || {});
            next.settings = Object.assign({background_class: '', padding: 'md', css_class: ''}, next.settings || {});
            next.columns = Array.isArray(next.columns) ? next.columns.map(function (column, columnIndex) {
                return normalizeColumn(column, next.uid, columnIndex);
            }) : [];
            syncSectionColumnsWithLayout(next);
            return next;
        }

        function normalizeColumn(column, sectionUid, columnIndex) {
            const next = Object.assign({}, column || {});
            next.uid = next.uid || (sectionUid + '-column-' + (columnIndex + 1));
            next.title = next.title || ('Колонка ' + (columnIndex + 1));
            next.visibility = Object.assign(defaultVisibility(), next.visibility || {});
            next.width = Object.assign(defaultColumnWidth(), next.width || {});
            next.settings = Object.assign({align: 'stretch', css_class: ''}, next.settings || {});
            next.nodes = Array.isArray(next.nodes) ? next.nodes.map(function (node, nodeIndex) {
                return normalizeNode(node, next.uid, nodeIndex);
            }) : [];
            return next;
        }

        function normalizeNode(node, columnUid, nodeIndex) {
            const next = Object.assign({}, node || {});
            next.uid = next.uid || (columnUid + '-node-' + (nodeIndex + 1));
            next.type = next.type || 'block';
            next.label = next.label || ('node.' + (nodeIndex + 1));
            next.class_name = next.class_name || '';
            next.notes = next.notes || '';
            next.source_key = next.source_key || '';
            next.device_visibility = Object.assign(defaultVisibility(), next.device_visibility || {});
            next.options = (next.options && typeof next.options === 'object') ? next.options : {};
            next.widget_id = Number(next.widget_id || 0);
            next.widget_name = next.widget_name || '';
            next.widget_controller = next.widget_controller || '';
            return next;
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function getDeviceTitle(device) {
            return deviceTitles[device] || device;
        }

        function getPageModeTitle(mode) {
            return pageModeTitles[mode] || mode;
        }

        function getPageStatusTitle(status) {
            return pageStatusTitles[status] || status;
        }

        function getLayoutOption(value) {
            return layoutOptions.find(function (option) {
                return option.value === value;
            }) || {value: value, title: value, hint: ''};
        }

        function getWidthOption(value) {
            return columnWidthOptions.find(function (option) {
                return option.value === value;
            }) || {value: value, title: value};
        }

        function getAlignOption(value) {
            return alignOptions.find(function (option) {
                return option.value === value;
            }) || {value: value, title: value};
        }

        function getNodeTypeTitle(type) {
            if (type === 'system_widget') {
                return 'Системный виджет';
            }
            return 'Блок';
        }

        function getBlockPreset(value) {
            return blockPresetMap[value] || null;
        }

        function getNodeDisplayLabel(node) {
            if (!node) {
                return '';
            }

            if (node.type === 'block') {
                const preset = getBlockPreset(node.source_key || node.label);
                if (preset) {
                    return preset.title;
                }
            }

            if (node.type === 'system_widget') {
                const widget = findWidgetById(node.widget_id);
                if (widget && widget.title) {
                    return widget.title;
                }
            }

            return node.label || 'Элемент';
        }

        function helpIcon(text) {
            return ' <span class="lb-help" data-toggle="tooltip" data-placement="top" title="' + escapeHtml(text) + '"><i class="fas fa-question-circle"></i></span>';
        }

        function fieldLabel(title, hint) {
            return '<label class="small text-muted d-block mb-1">' + escapeHtml(title) + (hint ? helpIcon(hint) : '') + '</label>';
        }

        function initTooltips(root) {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.tooltip) {
                return;
            }

            const $root = root ? window.jQuery(root) : window.jQuery(document);
            $root.find('[data-toggle="tooltip"]').tooltip({container: 'body'});
        }

        function renderPageMeta() {
            return 'Ключ страницы: <code>' + escapeHtml(state.page.key) + '</code> | Режим: ' + escapeHtml(getPageModeTitle(state.page.mode)) + ' | Статус: ' + escapeHtml(getPageStatusTitle(state.page.status));
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

        function isVisibleOnDevice(visibility) {
            if (!visibility || typeof visibility !== 'object') {
                return true;
            }
            return visibility[state.activeDevice] !== false;
        }

        function getLayoutColumnCount(layout) {
            if (layout === '1col') {
                return 1;
            }
            if (layout.indexOf('3col') === 0) {
                return 3;
            }
            return 2;
        }

        function syncSectionColumnsWithLayout(section) {
            const required = getLayoutColumnCount(section.layout);
            section.columns = Array.isArray(section.columns) ? section.columns : [];

            while (section.columns.length < required) {
                section.columns.push(normalizeColumn({title: 'Колонка ' + (section.columns.length + 1)}, section.uid, section.columns.length));
            }

            if (section.columns.length > required) {
                const overflow = section.columns.splice(required);
                overflow.forEach(function (column) {
                    if (column && Array.isArray(column.nodes)) {
                        section.columns[required - 1].nodes = section.columns[required - 1].nodes.concat(column.nodes);
                    }
                });
            }

            section.columns = section.columns.map(function (column, columnIndex) {
                return normalizeColumn(column, section.uid, columnIndex);
            });
        }

        function moveArrayItem(items, fromIndex, toIndex) {
            const list = items.slice();
            const chunk = list.splice(fromIndex, 1);
            if (!chunk.length) {
                return list;
            }
            list.splice(toIndex, 0, chunk[0]);
            return list;
        }

        function setDeepValue(target, path, value) {
            const parts = path.split('.');
            let current = target;

            for (let index = 0; index < parts.length - 1; index += 1) {
                if (!current[parts[index]] || typeof current[parts[index]] !== 'object') {
                    current[parts[index]] = {};
                }
                current = current[parts[index]];
            }

            current[parts[parts.length - 1]] = value;
        }

        function getSelectionTarget() {
            if (!state.selection) {
                return null;
            }

            if (state.selection.type === 'section') {
                return state.schema.sections[state.selection.sectionIndex] || null;
            }

            if (state.selection.type === 'column') {
                return getSelectedColumn();
            }

            return getSelectedNode();
        }

        function renderVisibilityControls(pathBase, visibility) {
            return state.screen.devices.map(function (device) {
                const inputId = 'lb-' + pathBase.replace(/\./g, '-') + '-' + device;
                return '' +
                    '<div class="form-check form-check-inline mr-2">' +
                        '<input class="form-check-input" type="checkbox" id="' + inputId + '" data-field="' + pathBase + '.' + device + '"' + (visibility[device] !== false ? ' checked' : '') + '>' +
                        '<label class="form-check-label small" for="' + inputId + '">' + escapeHtml(getDeviceTitle(device)) + '</label>' +
                    '</div>';
            }).join('');
        }

        function renderWidthControls(width) {
            return state.screen.devices.map(function (device) {
                const widthOption = getWidthOption(String(width[device] || 'auto'));
                return '' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Ширина для устройства «' + getDeviceTitle(device) + '»', 'Задает, сколько места колонка занимает на выбранном типе устройства.') +
                        '<select class="form-control form-control-sm" data-field="width.' + device + '">' +
                            columnWidthOptions.map(function (option) {
                                return '<option value="' + option.value + '"' + (String(width[device] || 'auto') === option.value ? ' selected' : '') + '>' + option.title + '</option>';
                            }).join('') +
                        '</select>' +
                        '<div class="small text-muted mt-1">Сейчас: ' + escapeHtml(widthOption.title) + '</div>' +
                    '</div>';
            }).join('');
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

            if (!widgetForm) {
                return;
            }
            const options = {};

            widgetForm.querySelectorAll('input, select, textarea').forEach(function (element) {
                if (!element.name) {
                    return;
                }

                if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
                    return;
                }

                if (element.tagName === 'SELECT' && element.multiple) {
                    options[element.name] = Array.from(element.selectedOptions).map(function (option) {
                        return option.value;
                    });
                    return;
                }

                if (element.type === 'checkbox') {
                    if (!Array.isArray(options[element.name])) {
                        options[element.name] = [];
                    }
                    options[element.name].push(element.value || '1');
                    return;
                }

                options[element.name] = element.value;
            });

            node.options = options;
        }

        function renderBlockLibrary() {
            blockList.innerHTML = blockPresets.map(function (block, index) {
                return '' +
                    '<button type="button" class="list-group-item list-group-item-action" data-role="insert-block" data-block-index="' + index + '">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<span class="font-weight-bold">' + escapeHtml(block.title) + '</span>' +
                            '<span class="badge badge-light">Добавить</span>' +
                        '</div>' +
                        '<div class="small text-muted lb-library-caption mt-1">' + escapeHtml(block.description) + '</div>' +
                    '</button>';
            }).join('');

            initTooltips(blockList);
        }

        function renderWidgetLibrary() {
            const groups = Object.keys(state.widgetsCatalog);
            if (!groups.length) {
                widgetList.innerHTML = '<div class="text-muted">Список виджетов пока пуст или еще не загружен.</div>';
                return;
            }

            widgetList.innerHTML = groups.map(function (group) {
                const items = state.widgetsCatalog[group] || [];
                return '' +
                    '<div class="mb-3">' +
                        '<div class="font-weight-bold text-uppercase small mb-2">' + escapeHtml(group === 'core' ? 'Система' : group) + '</div>' +
                        items.map(function (widget) {
                            return '' +
                                '<button type="button" class="list-group-item list-group-item-action mb-1 border rounded" data-role="insert-widget" data-widget-id="' + widget.id + '">' +
                                    '<div class="font-weight-bold">' + escapeHtml(widget.title) + '</div>' +
                                    '<div class="text-muted small">Код: ' + escapeHtml((widget.controller || 'core') + '.' + widget.name) + '</div>' +
                                '</button>';
                        }).join('') +
                    '</div>';
            }).join('');

            initTooltips(widgetList);
        }

        function renderCanvas() {
            if (!state.schema.sections.length) {
                canvasRoot.innerHTML = '<div class="alert alert-light border">Секция ещё не добавлена. Нажми «Добавить секцию».</div>';
                renderInspector();
                return;
            }

            canvasRoot.innerHTML = state.schema.sections.map(function (section, sectionIndex) {
                const sectionVisible = isVisibleOnDevice(section.visibility);
                const layoutTitle = getLayoutOption(section.layout).title;
                return '' +
                    '<div class="border rounded p-3 mb-3 lb-section' + (state.selection && state.selection.sectionIndex === sectionIndex ? ' border-primary' : '') + (!sectionVisible ? ' lb-muted-device' : '') + '" data-role="section" data-section-index="' + sectionIndex + '" data-drag-kind="section" draggable="true">' +
                        '<div class="d-flex justify-content-between align-items-center mb-3">' +
                            '<div>' +
                                '<div class="d-flex align-items-center">' +
                                    '<strong>' + escapeHtml(section.title) + '</strong>' +
                                    '<span class="badge badge-light ml-2">Перетащить</span>' +
                                    (!sectionVisible ? '<span class="badge badge-warning ml-2">Скрыто на устройстве «' + escapeHtml(getDeviceTitle(state.activeDevice)) + '»</span>' : '') +
                                '</div>' +
                                '<div class="small text-muted">Схема колонок: ' + escapeHtml(layoutTitle) + (section.settings.background_class ? ' | фон: ' + escapeHtml(section.settings.background_class) : '') + '</div>' +
                            '</div>' +
                            '<div class="btn-group btn-group-sm">' +
                                '<button type="button" class="btn btn-outline-danger" data-action="delete-section" data-section-index="' + sectionIndex + '">Удалить</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="row">' +
                            section.columns.map(function (column, columnIndex) {
                                const columnVisible = isVisibleOnDevice(column.visibility);
                                return '' +
                                    '<div class="' + sectionColumnClass(section.columns.length) + ' mb-3">' +
                                        '<div class="border rounded p-2 h-100 bg-light lb-column' + (state.selection && state.selection.sectionIndex === sectionIndex && state.selection.columnIndex === columnIndex ? ' border-primary' : '') + (!columnVisible ? ' lb-muted-device' : '') + '" data-role="column" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '">' +
                                            '<div class="d-flex justify-content-between align-items-center mb-2">' +
                                                '<div class="small font-weight-bold">' + escapeHtml(column.title) + '</div>' +
                                                '<div class="small text-muted">Ширина: ' + escapeHtml(getWidthOption(String(column.width[state.activeDevice] || 'auto')).title) + '</div>' +
                                            '</div>' +
                                            '<div class="small text-muted mb-2">Нажмите, чтобы выбрать колонку. Перетаскивание в эту область отправляет элемент в конец колонки.</div>' +
                                            '<div>' +
                                                column.nodes.map(function (node, nodeIndex) {
                                                    const nodeVisible = isVisibleOnDevice(node.device_visibility);
                                                    const nodeLabel = getNodeDisplayLabel(node);
                                                    return '' +
                                                        '<div class="border rounded bg-white p-2 mb-2 lb-node' + (state.selection && state.selection.type === 'node' && state.selection.sectionIndex === sectionIndex && state.selection.columnIndex === columnIndex && state.selection.nodeIndex === nodeIndex ? ' border-primary' : '') + (!nodeVisible ? ' lb-muted-device' : '') + '" data-role="node" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '" data-node-index="' + nodeIndex + '" data-drag-kind="node" draggable="true">' +
                                                            '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                                                '<div class="small text-muted text-uppercase">' + escapeHtml(getNodeTypeTitle(node.type)) + '</div>' +
                                                                '<button type="button" class="btn btn-link btn-sm text-danger p-0" data-action="delete-node" data-section-index="' + sectionIndex + '" data-column-index="' + columnIndex + '" data-node-index="' + nodeIndex + '">удалить</button>' +
                                                            '</div>' +
                                                            '<div class="d-flex align-items-center justify-content-between">' +
                                                                '<div>' + escapeHtml(nodeLabel) + '</div>' +
                                                                '<span class="badge badge-light ml-2">Перетащить</span>' +
                                                            '</div>' +
                                                            '<div class="small text-muted mt-1">' + (node.class_name ? 'Стиль: ' + escapeHtml(node.class_name) : 'Дополнительный стиль не задан') + (!nodeVisible ? ' | скрыто на устройстве «' + escapeHtml(getDeviceTitle(state.activeDevice)) + '»' : '') + '</div>' +
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
            initTooltips(canvasRoot);
        }

        function renderInspector() {
            const selection = state.selection;

            if (!selection) {
                selectionSummary.textContent = 'Ничего не выбрано';
                selectionControls.innerHTML = '';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                return;
            }

            if (selection.type === 'section') {
                const section = state.schema.sections[selection.sectionIndex];
                selectionSummary.innerHTML = '<strong>Секция</strong><br><span class="text-muted">' + escapeHtml(section.title) + '</span>';
                selectionControls.innerHTML = '' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Название секции', 'Это имя видят редакторы внутри конструктора. На сайте его можно не показывать.') +
                        '<input type="text" class="form-control form-control-sm" data-field="title" value="' + escapeHtml(section.title) + '">' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Схема колонок', 'Определяет, сколько колонок будет в секции и как они распределяются по ширине.') +
                        '<select class="form-control form-control-sm" data-field="layout">' +
                            layoutOptions.map(function (option) {
                                return '<option value="' + option.value + '"' + (section.layout === option.value ? ' selected' : '') + '>' + option.title + '</option>';
                            }).join('') +
                        '</select>' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Класс фона', 'Нужен, если разработчик заранее подготовил готовые стили фона. Можно оставить пустым.') +
                        '<input type="text" class="form-control form-control-sm" data-field="settings.background_class" value="' + escapeHtml(section.settings.background_class || '') + '">' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Дополнительный стиль', 'Служебное поле для подключения особого оформления секции. Оставьте пустым, если не используете стили вручную.') +
                        '<input type="text" class="form-control form-control-sm" data-field="settings.css_class" value="' + escapeHtml(section.settings.css_class || '') + '">' +
                    '</div>' +
                    '<div class="form-group mb-3">' +
                        fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть секцию на компьютере, планшете или телефоне.') +
                        renderVisibilityControls('visibility', section.visibility) +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-section" data-section-index="' + selection.sectionIndex + '">Удалить секцию</button>';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                initTooltips(selectionControls);
                return;
            }

            if (selection.type === 'column') {
                const column = getSelectedColumn();
                selectionSummary.innerHTML = '<strong>Колонка</strong><br><span class="text-muted">' + escapeHtml(column ? column.title : 'Колонка') + '</span>';
                selectionControls.innerHTML = '' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Название колонки', 'Служебное имя для редактора. Помогает не путаться в сложных секциях.') +
                        '<input type="text" class="form-control form-control-sm" data-field="title" value="' + escapeHtml(column.title) + '">' +
                    '</div>' +
                    renderWidthControls(column.width) +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Выравнивание содержимого', 'Помогает прижать содержимое колонки к верху, центру или низу.') +
                        '<select class="form-control form-control-sm" data-field="settings.align">' +
                            alignOptions.map(function (option) {
                                return '<option value="' + option.value + '"' + (column.settings.align === option.value ? ' selected' : '') + '>' + option.title + '</option>';
                            }).join('') +
                        '</select>' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Дополнительный стиль', 'Служебное поле для особого оформления колонки. Можно оставить пустым.') +
                        '<input type="text" class="form-control form-control-sm" data-field="settings.css_class" value="' + escapeHtml(column.settings.css_class || '') + '">' +
                    '</div>' +
                    '<div class="form-group mb-2">' +
                        fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть колонку на нужных типах устройств.') +
                        renderVisibilityControls('visibility', column.visibility) +
                    '</div>' +
                    '<div class="small text-muted">В эту колонку можно перетаскивать блоки и виджеты из других колонок.</div>';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                initTooltips(selectionControls);
                return;
            }

            const node = getSelectedNode();
            if (!node) {
                selectionSummary.textContent = 'Выбранный элемент не найден';
                selectionControls.innerHTML = '';
                widgetForm.innerHTML = 'Выберите системный виджет на макете, чтобы открыть его штатные настройки.';
                return;
            }

            selectionSummary.innerHTML = '<strong>' + escapeHtml(getNodeDisplayLabel(node)) + '</strong><br><span class="text-muted">Тип: ' + escapeHtml(getNodeTypeTitle(node.type)) + '</span>';
            selectionControls.innerHTML = '' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Название элемента', 'Короткое понятное имя, по которому редактор узнает блок внутри конструктора.') +
                    '<input type="text" class="form-control form-control-sm" data-field="label" value="' + escapeHtml(getNodeDisplayLabel(node)) + '">' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Дополнительный стиль', 'Служебное поле для особого оформления конкретного элемента.') +
                    '<input type="text" class="form-control form-control-sm" data-field="class_name" value="' + escapeHtml(node.class_name || '') + '">' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Ключ источника данных', 'Нужен, если блок должен подтягивать данные из заранее заданного источника или сценария.') +
                    '<input type="text" class="form-control form-control-sm" data-field="source_key" value="' + escapeHtml(node.source_key || '') + '">' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Заметки для редактора', 'Сюда можно записать, зачем нужен блок или что в нем важно не забыть.') +
                    '<textarea class="form-control form-control-sm" rows="3" data-field="notes">' + escapeHtml(node.notes || '') + '</textarea>' +
                '</div>' +
                '<div class="form-group mb-2">' +
                    fieldLabel('Показывать на устройствах', 'Можно отдельно скрыть этот элемент на нужных типах устройств.') +
                    renderVisibilityControls('device_visibility', node.device_visibility) +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-node" data-section-index="' + selection.sectionIndex + '" data-column-index="' + selection.columnIndex + '" data-node-index="' + selection.nodeIndex + '">Удалить элемент</button>';

            if (node.type === 'system_widget' && node.widget_id) {
                loadWidgetOptions(node);
            } else if (node.type === 'system_widget') {
                widgetForm.innerHTML = 'У этого виджета пока нет связи с системным каталогом. Добавьте его заново из списка слева.';
            } else {
                widgetForm.innerHTML = 'Для этого блока сейчас доступны базовые настройки: название, видимость, заметки и связь с источником данных.';
            }

            initTooltips(selectionControls);
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
            widgetList.innerHTML = '<div class="text-muted">Загрузка списка виджетов...</div>';

            const response = await fetch(state.screen.api.widgets_catalog_url, {
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin'
            });

            const result = await response.json();
            if (result.error) {
                widgetList.innerHTML = '<div class="text-danger">Не удалось загрузить список виджетов.</div>';
                return;
            }

            state.widgetsCatalog = result.widgets || {};
            renderWidgetLibrary();
            renderCanvas();
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
                versionsList.innerHTML = '<div class="text-muted">Версий пока нет. Первая появится после первого сохранения страницы.</div>';
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
                widgetForm.innerHTML = '<div class="text-danger">Не удалось загрузить форму настроек виджета.</div>';
                return;
            }

            widgetForm.innerHTML = result.html;
            initTooltips(widgetForm);
        }

        function moveSection(fromIndex, targetIndex) {
            if (fromIndex === targetIndex || fromIndex < 0 || targetIndex < 0) {
                return;
            }

            const toIndex = fromIndex < targetIndex ? targetIndex - 1 : targetIndex;
            state.schema.sections = moveArrayItem(state.schema.sections, fromIndex, toIndex);
            state.selection = {type: 'section', sectionIndex: toIndex};
        }

        function moveNode(fromSectionIndex, fromColumnIndex, fromNodeIndex, toSectionIndex, toColumnIndex, toNodeIndex) {
            const sourceSection = state.schema.sections[fromSectionIndex];
            const targetSection = state.schema.sections[toSectionIndex];

            if (!sourceSection || !targetSection) {
                return;
            }

            const sourceColumn = sourceSection.columns[fromColumnIndex];
            const targetColumn = targetSection.columns[toColumnIndex];

            if (!sourceColumn || !targetColumn) {
                return;
            }

            const movedNodes = sourceColumn.nodes.splice(fromNodeIndex, 1);
            if (!movedNodes.length) {
                return;
            }

            const node = movedNodes[0];
            let insertIndex = typeof toNodeIndex === 'number' ? toNodeIndex : targetColumn.nodes.length;

            if (sourceColumn === targetColumn && fromNodeIndex < insertIndex) {
                insertIndex -= 1;
            }

            targetColumn.nodes.splice(insertIndex, 0, node);
            state.selection = {
                type: 'node',
                sectionIndex: toSectionIndex,
                columnIndex: toColumnIndex,
                nodeIndex: insertIndex
            };
        }

        function setSelection(selection) {
            syncSelectedWidgetFormIntoState();
            state.selection = selection;
            renderCanvas();
        }

        function addSection() {
            const title = window.prompt('Название новой секции', 'Новая секция');
            if (!title) {
                return;
            }

            const layout = '2col_equal';
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
                visibility: defaultVisibility(),
                settings: {background_class: '', padding: 'md', css_class: ''},
                columns: columns
            });

            setSelection({type: 'section', sectionIndex: state.schema.sections.length - 1});
        }

        function insertBlock(blockIndex) {
            const column = getSelectedColumn();
            if (!column) {
                window.alert('Сначала выберите колонку на макете страницы.');
                return;
            }

            const block = blockPresets[blockIndex];
            if (!block) {
                return;
            }

            column.nodes.push({
                uid: uid('node'),
                type: 'block',
                label: block.title,
                class_name: '',
                notes: '',
                source_key: block.key,
                device_visibility: defaultVisibility(),
                options: {}
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
                window.alert('Сначала выберите колонку на макете страницы.');
                return;
            }

            const widget = findWidgetById(widgetId);
            if (!widget) {
                window.alert('Виджет не найден в каталоге.');
                return;
            }

            column.nodes.push({
                uid: uid('node'),
                type: 'system_widget',
                label: widget.title || 'Системный виджет',
                widget_id: widget.id,
                widget_name: widget.name,
                widget_controller: widget.controller,
                options: {},
                device_visibility: defaultVisibility(),
                class_name: '',
                notes: '',
                source_key: ''
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
            body.set('version_note', versionNote.value || 'Сохранение страницы');

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
                window.alert(result.message || 'Не удалось сохранить страницу');
                return;
            }

            state.page = Object.assign({}, state.page, result.page || {});
            updatedAt.textContent = state.page.updated_at || '';
            pageMeta.innerHTML = renderPageMeta();
            canvasStatus.textContent = 'Изменения сохранены';
            versionNote.value = '';
            await loadVersions();
            renderCanvas();
        }

        function clearDropTargets() {
            canvasRoot.querySelectorAll('.lb-drop-target').forEach(function (element) {
                element.classList.remove('lb-drop-target');
            });
        }

        function getDropElement(event) {
            if (!state.drag) {
                return null;
            }

            if (state.drag.type === 'section') {
                return event.target.closest('[data-role="section"]');
            }

            return event.target.closest('[data-role="node"], [data-role="column"]');
        }

        async function restoreVersion(versionId) {
            if (!window.confirm('Восстановить выбранную сохраненную версию страницы?')) {
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
                renderCanvas();
            });
        });

        document.getElementById('lb-add-section').addEventListener('click', addSection);
        document.getElementById('lb-save-canvas').addEventListener('click', function () {
            saveCanvas().catch(function (error) {
                console.error(error);
                window.alert('Ошибка сохранения страницы');
            });
        });
        document.getElementById('lb-reload-widgets').addEventListener('click', function () {
            loadWidgetCatalog().catch(function (error) {
                console.error(error);
                widgetList.innerHTML = '<div class="text-danger">Ошибка загрузки списка виджетов.</div>';
            });
        });
        document.getElementById('lb-refresh-versions').addEventListener('click', function () {
            loadVersions().catch(function (error) {
                console.error(error);
            });
        });

        selectionControls.addEventListener('change', function (event) {
            const field = event.target.closest('[data-field]');
            if (!field) {
                return;
            }

            const target = getSelectionTarget();
            if (!target) {
                return;
            }

            syncSelectedWidgetFormIntoState();

            const value = field.type === 'checkbox' ? field.checked : field.value;
            setDeepValue(target, field.dataset.field, value);

            if (state.selection && state.selection.type === 'section' && field.dataset.field === 'layout') {
                syncSectionColumnsWithLayout(target);
            }

            renderCanvas();
        });

        function handleActionTarget(actionTarget) {
            if (!actionTarget) {
                return false;
            }

            const sectionIndex = Number(actionTarget.dataset.sectionIndex);
            const columnIndex = Number(actionTarget.dataset.columnIndex);
            const nodeIndex = Number(actionTarget.dataset.nodeIndex);
            const action = actionTarget.dataset.action;

            if (action === 'delete-section') {
                if (window.confirm('Удалить секцию?')) {
                    syncSelectedWidgetFormIntoState();
                    state.schema.sections.splice(sectionIndex, 1);
                    state.selection = null;
                    renderCanvas();
                }
                return true;
            }

            if (action === 'delete-node') {
                if (window.confirm('Удалить элемент?')) {
                    syncSelectedWidgetFormIntoState();
                    state.schema.sections[sectionIndex].columns[columnIndex].nodes.splice(nodeIndex, 1);
                    state.selection = null;
                    renderCanvas();
                }
                return true;
            }

            return false;
        }

        selectionControls.addEventListener('click', function (event) {
            const actionTarget = event.target.closest('[data-action]');
            if (handleActionTarget(actionTarget)) {
                event.preventDefault();
            }
        });

        canvasRoot.addEventListener('dragstart', function (event) {
            const target = event.target.closest('[data-drag-kind]');
            if (!target) {
                return;
            }

            syncSelectedWidgetFormIntoState();

            if (target.dataset.dragKind === 'section') {
                state.drag = {
                    type: 'section',
                    sectionIndex: Number(target.dataset.sectionIndex)
                };
            }

            if (target.dataset.dragKind === 'node') {
                state.drag = {
                    type: 'node',
                    sectionIndex: Number(target.dataset.sectionIndex),
                    columnIndex: Number(target.dataset.columnIndex),
                    nodeIndex: Number(target.dataset.nodeIndex)
                };
            }

            target.classList.add('lb-dragging');

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', target.dataset.dragKind || 'drag');
            }
        });

        canvasRoot.addEventListener('dragend', function () {
            state.drag = null;
            clearDropTargets();
            canvasRoot.querySelectorAll('.lb-dragging').forEach(function (element) {
                element.classList.remove('lb-dragging');
            });
        });

        canvasRoot.addEventListener('dragover', function (event) {
            const target = getDropElement(event);

            if (!target && state.drag && state.drag.type === 'section') {
                event.preventDefault();
                clearDropTargets();
                return;
            }

            if (!target) {
                return;
            }

            event.preventDefault();
            clearDropTargets();
            target.classList.add('lb-drop-target');
        });

        canvasRoot.addEventListener('drop', function (event) {
            if (!state.drag) {
                return;
            }

            event.preventDefault();

            const target = getDropElement(event);

            if (state.drag.type === 'section') {
                if (target) {
                    moveSection(state.drag.sectionIndex, Number(target.dataset.sectionIndex));
                } else {
                    const fromIndex = state.drag.sectionIndex;
                    const lastIndex = state.schema.sections.length - 1;
                    if (fromIndex !== lastIndex) {
                        state.schema.sections = moveArrayItem(state.schema.sections, fromIndex, lastIndex);
                        state.selection = {type: 'section', sectionIndex: lastIndex};
                    }
                }

                renderCanvas();
                clearDropTargets();
                return;
            }

            if (!target) {
                clearDropTargets();
                return;
            }

            if (target.dataset.role === 'column') {
                moveNode(
                    state.drag.sectionIndex,
                    state.drag.columnIndex,
                    state.drag.nodeIndex,
                    Number(target.dataset.sectionIndex),
                    Number(target.dataset.columnIndex)
                );
            }

            if (target.dataset.role === 'node') {
                moveNode(
                    state.drag.sectionIndex,
                    state.drag.columnIndex,
                    state.drag.nodeIndex,
                    Number(target.dataset.sectionIndex),
                    Number(target.dataset.columnIndex),
                    Number(target.dataset.nodeIndex)
                );
            }

            renderCanvas();
            clearDropTargets();
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
            widgetList.innerHTML = '<div class="text-danger">Ошибка загрузки списка виджетов.</div>';
        });
        initTooltips(document);
    })();
</script>
<?php $this->addBottom(ob_get_clean()); ?>