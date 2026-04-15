(function () {
    function boot() {
    var config = window.NORDICSTYL_INJECT_CONFIG || {};

    if (!config.enabled) {
        return;
    }

    var doc = document;
    var root = doc.documentElement;
    var body = doc.body;

    if (!body) {
        return;
    }
    var overlay = null;
    var panel = null;
    var previewStyleNode = null;
    var selectedElement = null;
    var selectedSelector = '';
    var selectedTargetKey = '';
    var selectedTitle = '';
    var selectedTargetSource = 'nordic id';
    var hoveredElement = null;
    var saveInFlight = false;
    var loadToken = 0;
    var deviceKey = 'base';
    var stateKey = 'default';
    var currentRule = createEmptyRule('');
    var layoutState = normalizeLayoutState(config.layout_state || {});
    var pageInfo = config.page && typeof config.page === 'object' ? config.page : {};
    var saveStateUrl = String(config.state_url || '');
    var instantSchemeUrl = String(config.instant_scheme_url || '');
    var instantMoveUrl = String(config.instant_move_url || '');
    var widgetOptionsUrl = String(config.widget_options_url || '');
    var widgetOptionsSaveUrl = String(config.widget_options_save_url || '');
    var builderMode = String(config.builder_mode || 'design').toLowerCase() === 'layout' ? 'layout' : 'design';
    var widgetLibrary = normalizeWidgetLibrary(config.widget_library || {});
    var instantAdapterState = {
        rows: [],
        loaded: false,
        loading: false
    };
    var instantDragState = {
        active: false,
        widgetUid: '',
        bindingPageId: 0,
        sourcePosition: '',
        hoverPosition: '',
        ignoreClickUntil: 0
    };
    var fields = {};
    var contentFields = {};
    var overlayNodes = {
        insertContext: null,
        contentHint: null,
        contentForm: null,
        contentSaveButton: null,
        draftRoot: null,
        handoffButton: null,
        instantMoveHint: null,
        instantMovePanel: null,
        instantMoveSelect: null,
        instantMoveButton: null,
        widgetOptionsHint: null,
        widgetOptionsPanel: null,
        widgetOptionsFormHost: null,
        widgetOptionsSaveButton: null
    };
    var GRID_TOTAL_COLUMNS = 12;
    var resizeState = {
        active: false,
        rowUid: '',
        leftColumnUid: '',
        rightColumnUid: '',
        startX: 0,
        startLeftWidth: 0,
        startRightWidth: 0,
        container: null,
        handle: null,
        tooltip: null,
        snapshot: null,
        ignoreClickUntil: 0
    };
    var isEditorShell = /(?:[?&])nordicstyl_editor=1(?:&|$)/.test(window.location.search);

    function isDesignMode() {
        return builderMode === 'design';
    }

    function isLayoutMode() {
        return builderMode === 'layout';
    }

    function createEmptyRule(selector, targetKey) {
        return {
            id: 0,
            title: '',
            path: selector || '',
            target_type: 'node',
            target_key: targetKey || '',
            styles: {},
            custom: {}
        };
    }

    function deepClone(value) {
        return JSON.parse(JSON.stringify(value || {}));
    }

    function stripHtml(value) {
        var probe;

        if (typeof value !== 'string' || !value) {
            return '';
        }

        probe = doc.createElement('div');
        probe.innerHTML = value;

        return String(probe.textContent || probe.innerText || '').replace(/\s+/g, ' ').trim();
    }

    function makeUid(prefix) {
        return String(prefix || 'node') + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function getColumnWidth(column) {
        return clamp(parseInt(column && column.width || GRID_TOTAL_COLUMNS, 10) || GRID_TOTAL_COLUMNS, 1, GRID_TOTAL_COLUMNS);
    }

    function formatColumnPercent(width) {
        return ((getColumnWidth({ width: width }) / GRID_TOTAL_COLUMNS) * 100).toFixed(1).replace(/\.0$/, '') + '%';
    }

    function normalizeItems(value) {
        if (Array.isArray(value)) {
            return value.map(function (item) {
                return String(item || '').trim();
            }).filter(Boolean);
        }

        if (typeof value === 'string') {
            return value.split(/\r?\n/).map(function (item) {
                return item.trim();
            }).filter(Boolean);
        }

        return [];
    }

    function normalizeLayoutState(rawState) {
        var normalized = rawState && typeof rawState === 'object' ? deepClone(rawState) : {};

        if (!normalized.desktop || typeof normalized.desktop !== 'object') {
            normalized.desktop = {};
        }

        if (!Array.isArray(normalized.desktop.rows)) {
            normalized.desktop.rows = [];
        }

        if (!normalized.desktop.overrides || typeof normalized.desktop.overrides !== 'object') {
            normalized.desktop.overrides = {};
        }

        if (!normalized.tablet || typeof normalized.tablet !== 'object') {
            normalized.tablet = { rows: null, overrides: {} };
        }

        if (!normalized.mobile || typeof normalized.mobile !== 'object') {
            normalized.mobile = { rows: null, overrides: {} };
        }

        return normalized;
    }

    function normalizeInstantRows(rawRows) {
        return Array.isArray(rawRows) ? rawRows.filter(function (row) {
            return row && typeof row === 'object';
        }) : [];
    }

    function getInstantRows() {
        return instantAdapterState.rows;
    }

    function getDraftRows() {
        return Array.isArray(layoutState.desktop.rows) ? layoutState.desktop.rows.filter(isDraftOnlyRow) : [];
    }

    function getRenderableRows() {
        return getInstantRows().concat(getDraftRows());
    }

    function normalizeWidgetLibrary(rawLibrary) {
        var items = rawLibrary && Array.isArray(rawLibrary.items) ? rawLibrary.items : [];

        return {
            items: items.filter(function (item) {
                return item && typeof item === 'object' && String(item.widget_name || '').trim();
            }).map(function (item) {
                return {
                    uid: String(item.uid || makeUid('library-widget')),
                    title: String(item.title || 'Виджет').trim() || 'Виджет',
                    category_key: String(item.category_key || 'system').trim() || 'system',
                    category_title: String(item.category_title || 'Системные').trim() || 'Системные',
                    source: String(item.source || 'system').trim() || 'system',
                    widget_id: parseInt(item.widget_id || 0, 10) || 0,
                    widget_name: String(item.widget_name || '').trim(),
                    widget_controller: String(item.widget_controller || '').trim(),
                    description: String(item.description || '').trim(),
                    has_options: Boolean(item.has_options)
                };
            })
        };
    }

    function getOverlayPresets() {
        return [
            {
                key: 'hero',
                title: 'Hero',
                description: 'Главный экран с заголовком, текстом и кнопкой.'
            },
            {
                key: 'features',
                title: 'Преимущества',
                description: 'Три колонки для выгод или сильных сторон.'
            },
            {
                key: 'cta',
                title: 'CTA',
                description: 'Короткий призыв к действию с одной кнопкой.'
            },
            {
                key: 'faq',
                title: 'FAQ',
                description: 'Блок частых вопросов с быстрым редактированием.'
            },
            {
                key: 'cards',
                title: 'Карточки',
                description: 'Три карточки для тарифов, услуг или кейсов.'
            },
            {
                key: 'html',
                title: 'HTML',
                description: 'Техническая секция-пустышка под ручную верстку.'
            }
        ];
    }

    function getCustomBlockPresets() {
        return [
            {
                key: 'text',
                title: 'Текстовый блок',
                description: 'Заголовок и абзац для быстрого смыслового наполнения.'
            },
            {
                key: 'cta',
                title: 'CTA блок',
                description: 'Короткий призыв и одна кнопка действия.'
            },
            {
                key: 'html',
                title: 'HTML блок',
                description: 'Заготовка под кастомную верстку без привязки к системному виджету.'
            }
        ];
    }

    function getRecommendedSystemBlocks() {
        var preferredKeys = [
            ':html',
            ':text',
            'forms:form',
            'content:list',
            'content:slider',
            'users:list',
            'search:search'
        ];
        var libraryMap = {};
        var result = [];

        widgetLibrary.items.forEach(function (item) {
            libraryMap[(item.widget_controller || '') + ':' + item.widget_name] = item;
        });

        preferredKeys.forEach(function (key) {
            var item = libraryMap[key];
            if (item) {
                result.push(item);
            }
        });

        if (result.length < 6) {
            widgetLibrary.items.some(function (item) {
                if (result.indexOf(item) !== -1) {
                    return false;
                }

                result.push(item);

                return result.length >= 6;
            });
        }

        return result;
    }

    function getDefaultPresetContent(presetKey, presetTitle) {
        var title = String(presetTitle || 'Секция').trim() || 'Секция';

        if (presetKey === 'hero') {
            return {
                eyebrow: 'Hero',
                heading: 'Главный заголовок',
                text: 'Коротко объясните, почему пользователю стоит остаться именно на этом экране.',
                button_text: 'Оставить заявку',
                button_url: '#lead-form',
                items: [],
                html: ''
            };
        }

        if (presetKey === 'features') {
            return {
                eyebrow: title,
                heading: 'Три причины выбрать нас',
                text: 'Ниже можно быстро поменять список преимуществ прямо поверх страницы.',
                button_text: '',
                button_url: '',
                items: ['Быстрый старт', 'Гибкая настройка', 'Поддержка команды'],
                html: ''
            };
        }

        if (presetKey === 'cta') {
            return {
                eyebrow: 'CTA',
                heading: 'Готовы начать?',
                text: 'Один короткий призыв и понятная следующая кнопка действия.',
                button_text: 'Связаться',
                button_url: '#contact',
                items: [],
                html: ''
            };
        }

        if (presetKey === 'faq') {
            return {
                eyebrow: 'FAQ',
                heading: 'Частые вопросы',
                text: 'Сразу после вставки заполните ключевые вопросы, чтобы блок не был пустым.',
                button_text: '',
                button_url: '',
                items: ['Сколько времени занимает запуск?', 'Можно ли адаптировать под свою страницу?', 'Что входит в поддержку?'],
                html: ''
            };
        }

        if (presetKey === 'cards') {
            return {
                eyebrow: title,
                heading: 'Карточки',
                text: 'Используйте строки ниже как заголовки карточек или тарифов.',
                button_text: '',
                button_url: '',
                items: ['Карточка 1', 'Карточка 2', 'Карточка 3'],
                html: ''
            };
        }

        if (presetKey === 'html') {
            return {
                eyebrow: 'HTML',
                heading: 'Пустой HTML-блок',
                text: 'Эта секция нужна как техническая заготовка для дальнейшей ручной верстки.',
                button_text: '',
                button_url: '',
                items: [],
                html: '<div class="draft-html-block">Ваш HTML</div>'
            };
        }

        return {
            eyebrow: title,
            heading: title,
            text: '',
            button_text: '',
            button_url: '',
            items: [],
            html: ''
        };
    }

    function normalizePresetContent(presetKey, rawContent, presetTitle) {
        var defaults = getDefaultPresetContent(presetKey, presetTitle || presetKey);
        var content = rawContent && typeof rawContent === 'object' ? rawContent : {};

        return {
            eyebrow: String(content.eyebrow || defaults.eyebrow || '').trim(),
            heading: String(content.heading || defaults.heading || '').trim(),
            text: String(content.text || defaults.text || '').trim(),
            button_text: String(content.button_text || defaults.button_text || '').trim(),
            button_url: String(content.button_url || defaults.button_url || '').trim(),
            items: normalizeItems(content.items && content.items.length ? content.items : defaults.items),
            html: String(content.html || defaults.html || '').trim()
        };
    }

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }

        return String(value || '').replace(/[^a-zA-Z0-9_\-]/g, function (char) {
            return '\\' + char;
        });
    }

    function cssAttrEscape(value) {
        return String(value || '')
            .replace(/\\/g, '\\\\')
            .replace(/"/g, '\\"');
    }

    function buildDeviceLabel(key) {
        var labels = {
            base: 'Desktop',
            desktop: 'Desktop',
            tablet: 'Tablet',
            mobile: 'Mobile'
        };

        return labels[key] || key || 'Desktop';
    }

    function buildStateLabel(key) {
        var labels = {
            default: 'Обычный',
            hover: 'Наведение'
        };

        return labels[key] || key || 'Обычный';
    }

    function buildNodeSelector(targetKey) {
        if (!targetKey) {
            return '';
        }

        return '[data-nordic-id="' + cssAttrEscape(targetKey) + '"]';
    }

    function getRequestedFocusKey() {
        var url;

        try {
            url = new URL(window.location.href);
            return String(url.searchParams.get('nordicstyl_focus') || '').trim();
        } catch (error) {
            return '';
        }
    }

    function getEditableNode(element) {
        var editableNode;

        if (!element || element.nodeType !== 1 || !element.closest) {
            return null;
        }

        editableNode = element.closest('[data-nordic-id]');

        if (!editableNode) {
            return null;
        }

        if (editableNode === body && body.getAttribute('data-nordic-root') === 'true' && element !== body) {
            return null;
        }

        return editableNode;
    }

    function resolveElementTarget(element) {
        var editableNode = getEditableNode(element);
        var targetKey = editableNode ? String(editableNode.getAttribute('data-nordic-id') || '').trim() : '';

        if (!editableNode || !targetKey) {
            return {
                element: null,
                selector: '',
                targetKey: '',
                title: '',
                sourceLabel: 'nordic id required',
                device: deviceKey,
                deviceLabel: buildDeviceLabel(deviceKey),
                state: stateKey,
                stateLabel: buildStateLabel(stateKey),
                modeLabel: buildDeviceLabel(deviceKey) + ' · ' + buildStateLabel(stateKey)
            };
        }

        return {
            element: editableNode,
            selector: buildNodeSelector(targetKey),
            targetKey: targetKey,
            title: guessTitle(editableNode),
            sourceLabel: 'nordic id',
            device: deviceKey,
            deviceLabel: buildDeviceLabel(deviceKey),
            state: stateKey,
            stateLabel: buildStateLabel(stateKey),
            modeLabel: buildDeviceLabel(deviceKey) + ' · ' + buildStateLabel(stateKey)
        };
    }

    function notifyContext() {
        notifyParent('nordicstyl-picker-context', {
            selector: selectedSelector || '',
            storage_path: selectedTargetKey ? ('node:' + selectedTargetKey) : '',
            target_key: selectedTargetKey || '',
            target_type: 'node',
            title: selectedTitle || '',
            source_label: selectedTargetSource || 'nordic id',
            device: deviceKey,
            device_label: buildDeviceLabel(deviceKey),
            state: stateKey,
            state_label: buildStateLabel(stateKey),
            mode_label: buildDeviceLabel(deviceKey) + ' · ' + buildStateLabel(stateKey)
        });
    }

    function guessTitle(element) {
        var text;

        if (!element || element.nodeType !== 1) {
            return 'Элемент';
        }

        if (element === body || element.getAttribute('data-nordic-root') === 'true' || element.getAttribute('data-nordic-role') === 'page.root') {
            return 'Страница';
        }

        text = String(element.getAttribute('data-nordic-label') || '').trim();
        if (text) {
            return text;
        }

        text = String(element.getAttribute('aria-label') || element.getAttribute('title') || '').trim();
        if (text) {
            return text;
        }

        text = String(element.textContent || '').replace(/\s+/g, ' ').trim();
        if (text) {
            return text.slice(0, 48);
        }

        return String(element.tagName || 'Элемент').toLowerCase();
    }

    function findNodeRecord(rows, targetKey) {
        var result = null;

        if (!targetKey || !Array.isArray(rows)) {
            return null;
        }

        rows.some(function (row) {
            if (!row || typeof row !== 'object') {
                return false;
            }

            if (String(row.uid || '') === targetKey) {
                result = {
                    kind: 'row',
                    title: String(row.title || 'Секция'),
                    node: row,
                    row: row,
                    column: null
                };
                return true;
            }

            return (Array.isArray(row.columns) ? row.columns : []).some(function (column) {
                if (!column || typeof column !== 'object') {
                    return false;
                }

                if (String(column.uid || '') === targetKey) {
                    result = {
                        kind: 'column',
                        title: String(column.title || 'Колонка'),
                        node: column,
                        row: row,
                        column: column
                    };
                    return true;
                }

                if ((Array.isArray(column.widgets) ? column.widgets : []).some(function (widget) {
                    if (!widget || typeof widget !== 'object') {
                        return false;
                    }

                    if (String(widget.uid || '') !== targetKey) {
                        return false;
                    }

                    result = {
                        kind: 'widget',
                        title: String(widget.title || 'Блок'),
                        node: widget,
                        row: row,
                        column: column
                    };

                    return true;
                })) {
                    return true;
                }

                result = findNodeRecord(column.nested_rows || [], targetKey);

                return Boolean(result);
            });
        });

        return result;
    }

    function createPresetRow(presetKey, presetTitle) {
        var rowUid = makeUid('row');
        var rowTitle = String(presetTitle || 'Секция').trim() || 'Секция';
        var presetContent = normalizePresetContent(presetKey, {}, rowTitle);

        function makeColumn(title, width) {
            return {
                uid: makeUid('column'),
                title: title,
                kind: 'column',
                width: width,
                hidden: false,
                meta: {},
                widgets: [],
                nested_rows: []
            };
        }

        if (presetKey === 'features' || presetKey === 'cards') {
            return {
                uid: rowUid,
                title: rowTitle,
                kind: 'row',
                width_mode: 'grid',
                hidden: false,
                meta: {
                    preset_key: presetKey,
                    content: presetContent
                },
                columns: [
                    makeColumn(rowTitle + ' 1', 4),
                    makeColumn(rowTitle + ' 2', 4),
                    makeColumn(rowTitle + ' 3', 4)
                ]
            };
        }

        return {
            uid: rowUid,
            title: rowTitle,
            kind: 'row',
            width_mode: presetKey === 'html' ? 'full' : 'grid',
            hidden: false,
            meta: {
                preset_key: presetKey,
                content: presetContent
            },
            columns: [makeColumn(rowTitle + ' контент', 12)]
        };
    }

    function createCustomBlock(presetKey, presetTitle) {
        var title = String(presetTitle || 'Блок').trim() || 'Блок';
        var content;

        if (presetKey === 'cta') {
            content = {
                heading: 'Готовы двигаться дальше?',
                text: 'Добавьте короткий оффер и переведите пользователя в следующее действие.',
                button_text: 'Оставить заявку',
                button_url: '#contact',
                html: ''
            };
        } else if (presetKey === 'html') {
            content = {
                heading: title,
                text: '',
                button_text: '',
                button_url: '',
                html: '<div class="custom-html-block">Ваш HTML блок</div>'
            };
        } else {
            content = {
                heading: 'Новый текстовый блок',
                text: 'Этот блок можно сразу стилизовать в design mode как обычный элемент страницы.',
                button_text: '',
                button_url: '',
                html: ''
            };
        }

        return {
            uid: makeUid('widget'),
            title: title,
            kind: 'widget',
            source: 'custom',
            widget_type: 'custom',
            widget_variant: presetKey,
            widget_name: '',
            widget_controller: '',
            widget_id: 0,
            bind_id: 0,
            position_name: '',
            is_enabled: true,
            has_options: false,
            can_edit_options: false,
            bind_config: {},
            content: content
        };
    }

    function createSystemBlock(libraryItem) {
        return {
            uid: makeUid('widget'),
            title: String(libraryItem.title || 'Виджет').trim() || 'Виджет',
            kind: 'widget',
            source: String(libraryItem.source || 'system').trim() || 'system',
            widget_type: 'system',
            widget_variant: '',
            widget_name: String(libraryItem.widget_name || '').trim(),
            widget_controller: String(libraryItem.widget_controller || '').trim(),
            widget_id: parseInt(libraryItem.widget_id || 0, 10) || 0,
            bind_id: 0,
            position_name: '',
            is_enabled: true,
            has_options: Boolean(libraryItem.has_options),
            can_edit_options: Boolean(libraryItem.has_options),
            bind_config: {
                title: String(libraryItem.title || 'Виджет').trim() || 'Виджет',
                template: String(pageInfo.template || '').trim(),
                tpl_wrap: 'wrapper',
                tpl_body: String(libraryItem.widget_name || '').trim(),
                options: {}
            },
            description: String(libraryItem.description || '').trim(),
            category_title: String(libraryItem.category_title || 'Системные').trim() || 'Системные'
        };
    }

    function isCustomWidget(widget) {
        return String(widget && widget.widget_type || '').trim() === 'custom' || String(widget && widget.source || '').trim() === 'custom';
    }

    function isSystemWidget(widget) {
        if (!widget || typeof widget !== 'object' || isCustomWidget(widget)) {
            return false;
        }

        return (parseInt(widget.widget_id || 0, 10) || 0) > 0 || String(widget.widget_name || '').trim() !== '';
    }

    function getWidgetBindConfig(widget) {
        var bindConfig = widget && widget.bind_config && typeof widget.bind_config === 'object' ? deepClone(widget.bind_config) : {};

        if (!bindConfig.options || typeof bindConfig.options !== 'object') {
            bindConfig.options = {};
        }

        if (!bindConfig.title) {
            bindConfig.title = String(widget && widget.title || 'Виджет').trim() || 'Виджет';
        }

        if (!bindConfig.template) {
            bindConfig.template = String(pageInfo.template || '').trim();
        }

        if (!bindConfig.tpl_wrap) {
            bindConfig.tpl_wrap = 'wrapper';
        }

        if (!bindConfig.tpl_body) {
            bindConfig.tpl_body = String(widget && widget.widget_name || '').trim();
        }

        return bindConfig;
    }

    function getSystemWidgetPreview(widget) {
        var bindConfig = getWidgetBindConfig(widget);
        var options = bindConfig.options || {};
        var contentPreview = stripHtml(String(options.content || '').trim());
        var preview = '';

        if (contentPreview) {
            preview = contentPreview.slice(0, 180);
            if (contentPreview.length > 180) {
                preview += '...';
            }
            return preview;
        }

        if (bindConfig.title && bindConfig.title !== widget.title) {
            return 'Переименован в draft-layout: ' + bindConfig.title;
        }

        return widget.description || 'Системный InstantCMS виджет. Публикация создаст или обновит реальное правило с этими настройками.';
    }

    function getContentFrameMount() {
        var slot = doc.querySelector('[data-nordic-role="shell.content-body.slot"]');
        if (slot) {
            return slot;
        }

        var shellContent = doc.querySelector('[data-nordic-role="shell.content-body"]');
        if (shellContent) {
            return shellContent;
        }

        var runtimeBody = doc.querySelector('[data-nordic-role="content.body"]');
        if (runtimeBody) {
            return runtimeBody;
        }

        var frame = doc.querySelector('[data-nordic-role="content.frame"]');
        if (!frame) {
            return null;
        }

        return frame.querySelector('[data-nordic-role="shell.content-body.slot"]') || frame;
    }

    function getLayoutRenderScope() {
        return getContentFrameMount();
    }

    function isGeneratedUid(prefix, uid) {
        return new RegExp('^' + prefix + '-[a-z0-9]+-[a-z0-9]+$', 'i').test(String(uid || '').trim());
    }

    function isDraftOnlyRow(row) {
        var rowMeta = row && row.meta && typeof row.meta === 'object' ? row.meta : {};

        if (!row || typeof row !== 'object') {
            return false;
        }

        if (isGeneratedUid('row', row.uid)) {
            return true;
        }

        return String(rowMeta.preset_key || '').trim() !== '';
    }

    function findMountedRowNode(rowUid) {
        var scope = getLayoutRenderScope();

        if (!scope || !rowUid) {
            return null;
        }

        return scope.querySelector('[data-nordic-role="layout-row"][data-nordic-id="' + cssAttrEscape(String(rowUid)) + '"]');
    }

    function findNextMountedRowNode(rows, startIndex) {
        var nextNode = null;

        if (!Array.isArray(rows)) {
            return null;
        }

        rows.slice(startIndex || 0).some(function (row) {
            nextNode = findMountedRowNode(String(row && row.uid || ''));
            return Boolean(nextNode);
        });

        return nextNode;
    }

    function clearLayoutRender(scope) {
        Array.prototype.forEach.call((scope || doc).querySelectorAll('.ns-live-runtime__row-overlay'), function (node) {
            if (node && node.parentNode) {
                node.parentNode.removeChild(node);
            }
        });

        Array.prototype.forEach.call((scope || doc).querySelectorAll('.ns-live-runtime__row--draft-host'), function (node) {
            if (node && node.parentNode) {
                node.parentNode.removeChild(node);
            }
        });

        Array.prototype.forEach.call((scope || doc).querySelectorAll('.ns-live-runtime__mounted-row'), function (node) {
            node.classList.remove('ns-live-runtime__mounted-row');
        });
    }

    function getRowPresetContent(row) {
        var rowMeta = row && row.meta && typeof row.meta === 'object' ? row.meta : {};
        var presetKey = String(rowMeta.preset_key || '').trim();

        if (!presetKey) {
            return null;
        }

        return normalizePresetContent(presetKey, rowMeta.content || {}, String(row.title || presetKey));
    }

    function renderRowLead(row) {
        var content = getRowPresetContent(row);
        var lead;
        var kicker;
        var heading;
        var text;
        var actions;
        var button;
        var items;

        if (!content) {
            return null;
        }

        lead = doc.createElement('div');
        lead.className = 'ns-live-runtime__lead';

        if (content.eyebrow) {
            kicker = doc.createElement('div');
            kicker.className = 'ns-live-runtime__eyebrow';
            kicker.textContent = content.eyebrow;
            lead.appendChild(kicker);
        }

        if (content.heading) {
            heading = doc.createElement('h2');
            heading.className = 'ns-live-runtime__heading';
            heading.textContent = content.heading;
            lead.appendChild(heading);
        }

        if (content.text) {
            text = doc.createElement('p');
            text.className = 'ns-live-runtime__text';
            text.textContent = content.text;
            lead.appendChild(text);
        }

        if (content.items.length) {
            items = doc.createElement('div');
            items.className = 'ns-live-runtime__chips';
            content.items.forEach(function (item) {
                var chip = doc.createElement('span');
                chip.className = 'ns-live-runtime__chip';
                chip.textContent = item;
                items.appendChild(chip);
            });
            lead.appendChild(items);
        }

        if (content.button_text) {
            actions = doc.createElement('div');
            actions.className = 'ns-live-runtime__actions';
            button = doc.createElement('a');
            button.className = 'ns-live-runtime__button';
            button.href = content.button_url || '#';
            button.textContent = content.button_text;
            actions.appendChild(button);
            lead.appendChild(actions);
        }

        if (content.html) {
            var code = doc.createElement('pre');
            code.className = 'ns-live-runtime__code';
            code.textContent = content.html;
            lead.appendChild(code);
        }

        return lead.childNodes.length ? lead : null;
    }

    function renderCustomWidgetBody(widget) {
        var bodyNode = doc.createElement('div');
        var content = widget && widget.content && typeof widget.content === 'object' ? widget.content : {};
        var titleNode;
        var textNode;
        var buttonNode;
        var codeNode;

        bodyNode.className = 'ns-live-runtime__block-body';

        if (content.heading) {
            titleNode = doc.createElement('div');
            titleNode.className = 'ns-live-runtime__block-heading';
            titleNode.textContent = content.heading;
            bodyNode.appendChild(titleNode);
        }

        if (content.text) {
            textNode = doc.createElement('div');
            textNode.className = 'ns-live-runtime__block-text';
            textNode.textContent = content.text;
            bodyNode.appendChild(textNode);
        }

        if (content.button_text) {
            buttonNode = doc.createElement('a');
            buttonNode.className = 'ns-live-runtime__button ns-live-runtime__button--inline';
            buttonNode.href = content.button_url || '#';
            buttonNode.textContent = content.button_text;
            bodyNode.appendChild(buttonNode);
        }

        if (content.html) {
            codeNode = doc.createElement('pre');
            codeNode.className = 'ns-live-runtime__code';
            codeNode.textContent = content.html;
            bodyNode.appendChild(codeNode);
        }

        return bodyNode;
    }

    function renderSystemWidgetBody(widget) {
        var bodyNode = doc.createElement('div');
        var textNode = doc.createElement('div');
        var metaNode = doc.createElement('div');
        var descriptor = [];
        var bindConfig = getWidgetBindConfig(widget);

        bodyNode.className = 'ns-live-runtime__block-body';

        textNode.className = 'ns-live-runtime__block-text';
        textNode.textContent = getSystemWidgetPreview(widget);
        bodyNode.appendChild(textNode);

        if (widget.widget_controller) {
            descriptor.push(widget.widget_controller);
        }
        if (widget.widget_name) {
            descriptor.push(widget.widget_name);
        }

        metaNode = doc.createElement('div');
        metaNode.className = 'ns-live-runtime__block-meta';
        if (bindConfig.options && Object.keys(bindConfig.options).length) {
            descriptor.push('настройки заданы');
        }
        metaNode.textContent = descriptor.join(' / ');
        bodyNode.appendChild(metaNode);

        return bodyNode;
    }

    function renderWidgetNode(widget) {
        var article = doc.createElement('article');
        var header = doc.createElement('div');
        var badge = doc.createElement('span');
        var title = doc.createElement('div');
        var bindingPageId = parseInt(widget && widget.binding_page_id || 0, 10) || 0;
        var positionName = String(widget && widget.position_name || '').trim();

        article.className = 'ns-live-runtime__block ns-live-runtime__block--' + ((widget.widget_type || 'system') === 'custom' ? 'custom' : 'system');
        article.setAttribute('data-nordic-id', String(widget.uid || makeUid('widget')));
        article.setAttribute('data-nordic-role', 'builder.block');
        article.setAttribute('data-nordic-label', String(widget.title || 'Блок'));

        if (bindingPageId > 0) {
            article.setAttribute('data-instant-binding-page-id', String(bindingPageId));
            article.setAttribute('data-instant-position', positionName);

            if (isLayoutMode()) {
                article.setAttribute('draggable', 'true');
                article.classList.add('ns-live-runtime__block--draggable');
            }
        }

        header.className = 'ns-live-runtime__block-header';
        badge.className = 'ns-live-runtime__badge';
        badge.textContent = (widget.widget_type || 'system') === 'custom' ? 'Custom' : 'System';
        title.className = 'ns-live-runtime__block-title';
        title.textContent = String(widget.title || 'Блок');
        header.appendChild(badge);
        header.appendChild(title);
        article.appendChild(header);
        article.appendChild((widget.widget_type || 'system') === 'custom' ? renderCustomWidgetBody(widget) : renderSystemWidgetBody(widget));

        return article;
    }

    function findRowByUid(rows, rowUid) {
        var result = null;

        if (!rowUid || !Array.isArray(rows)) {
            return null;
        }

        rows.some(function (row) {
            if (!row || typeof row !== 'object') {
                return false;
            }

            if (String(row.uid || '') === String(rowUid)) {
                result = row;
                return true;
            }

            return (Array.isArray(row.columns) ? row.columns : []).some(function (column) {
                result = findRowByUid(column.nested_rows || [], rowUid);
                return Boolean(result);
            });
        });

        return result;
    }

    function findRowColumnsByBoundary(rowUid, leftColumnUid, rightColumnUid) {
        var row = findRowByUid(layoutState.desktop.rows, rowUid);
        var columns;
        var leftColumn = null;
        var rightColumn = null;

        if (!row) {
            return null;
        }

        columns = Array.isArray(row.columns) ? row.columns : [];
        columns.some(function (column) {
            if (String(column.uid || '') === String(leftColumnUid)) {
                leftColumn = column;
            }
            if (String(column.uid || '') === String(rightColumnUid)) {
                rightColumn = column;
            }
            return Boolean(leftColumn && rightColumn);
        });

        if (!leftColumn || !rightColumn) {
            return null;
        }

        return {
            row: row,
            columns: columns,
            leftColumn: leftColumn,
            rightColumn: rightColumn
        };
    }

    function renderColumnResizeHandles(columnsNode, row) {
        var columns = Array.isArray(row.columns) ? row.columns : [];
        var cumulative = 0;

        if (!isLayoutMode() || columns.length < 2 || String(row && row.meta && row.meta.adapter_source || '') === 'instant') {
            return;
        }

        columnsNode.classList.add('ns-live-runtime__columns--resizable');

        columns.forEach(function (column, index) {
            var handle;
            var width;

            width = getColumnWidth(column);
            cumulative += width;

            if (index === columns.length - 1) {
                return;
            }

            handle = doc.createElement('button');
            handle.className = 'ns-live-runtime__resize-handle';
            handle.type = 'button';
            handle.setAttribute('aria-label', 'Изменить ширину колонок');
            handle.setAttribute('data-resize-row', String(row.uid || ''));
            handle.setAttribute('data-resize-left', String(column.uid || ''));
            handle.setAttribute('data-resize-right', String(columns[index + 1].uid || ''));
            handle.style.left = ((cumulative / GRID_TOTAL_COLUMNS) * 100) + '%';
            columnsNode.appendChild(handle);
        });
    }

    function renderColumnNode(column) {
        var node = doc.createElement('div');
        var label = doc.createElement('div');
        var widthBadge = doc.createElement('span');
        var widgets = Array.isArray(column.widgets) ? column.widgets : [];
        var nestedRows = Array.isArray(column.nested_rows) ? column.nested_rows : [];
        var bodyNode = doc.createElement('div');
        var width = getColumnWidth(column);
        var meta = column && column.meta && typeof column.meta === 'object' ? column.meta : {};
        var positionName = String(meta.position_name || '').trim();

        node.className = 'ns-live-runtime__column';
        node.style.setProperty('--ns-col-span', String(width));
        node.setAttribute('data-nordic-id', String(column.uid || makeUid('column')));
        node.setAttribute('data-nordic-role', 'builder.column');
        node.setAttribute('data-nordic-label', String(column.title || 'Колонка'));

        if (positionName !== '') {
            node.setAttribute('data-instant-position', positionName);
            node.classList.add('ns-live-runtime__column--instant-target');
        }

        label.className = 'ns-live-runtime__label';
        label.textContent = String(column.title || 'Колонка');
        widthBadge.className = 'ns-live-runtime__width-badge';
        widthBadge.textContent = formatColumnPercent(width);
        node.appendChild(label);
        node.appendChild(widthBadge);

        bodyNode.className = 'ns-live-runtime__column-body';

        widgets.forEach(function (widget) {
            bodyNode.appendChild(renderWidgetNode(widget));
        });

        nestedRows.forEach(function (row) {
            bodyNode.appendChild(renderRowNode(row));
        });

        if (!widgets.length && !nestedRows.length) {
            var emptyNode = doc.createElement('div');
            emptyNode.className = 'ns-live-runtime__empty';
            emptyNode.textContent = 'Пустая колонка для нового блока';
            bodyNode.appendChild(emptyNode);
        }

        node.appendChild(bodyNode);

        return node;
    }

    function renderRowOverlayNode(row) {
        var overlayNode = doc.createElement('div');
        var header = doc.createElement('div');
        var label = doc.createElement('div');
        var columnsNode = doc.createElement('div');

        overlayNode.className = 'ns-live-runtime__row-overlay';

        header.className = 'ns-live-runtime__overlay-header';
        label.className = 'ns-live-runtime__label';
        label.textContent = String(row.title || 'Секция');
        header.appendChild(label);
        overlayNode.appendChild(header);

        columnsNode.className = 'ns-live-runtime__columns';
        (Array.isArray(row.columns) ? row.columns : []).forEach(function (column) {
            columnsNode.appendChild(renderColumnNode(column));
        });
        renderColumnResizeHandles(columnsNode, row);
        overlayNode.appendChild(columnsNode);

        return overlayNode;
    }

    function renderRowNode(row) {
        var section = doc.createElement('section');
        var lead = renderRowLead(row);
        var columnsNode = doc.createElement('div');

        section.className = 'ns-live-runtime__row ns-live-runtime__row--draft-host ns-live-runtime__row--' + (String(row.width_mode || 'grid') === 'full' ? 'full' : 'grid');
        section.setAttribute('data-nordic-id', String(row.uid || makeUid('row')));
        section.setAttribute('data-nordic-role', 'builder.row');
        section.setAttribute('data-nordic-label', String(row.title || 'Секция'));

        if (lead) {
            section.appendChild(lead);
        }

        columnsNode.className = 'ns-live-runtime__columns';
        (Array.isArray(row.columns) ? row.columns : []).forEach(function (column) {
            columnsNode.appendChild(renderColumnNode(column));
        });
        renderColumnResizeHandles(columnsNode, row);
        section.appendChild(columnsNode);

        return section;
    }

    function renderLayoutState() {
        var scope = getLayoutRenderScope();
        var instantRows = getInstantRows();
        var draftRows = getDraftRows();

        if (!scope) {
            return;
        }

        clearLayoutRender(scope);

        if (!instantRows.length && !draftRows.length) {
            return;
        }

        instantRows.forEach(function (row) {
            var mountedRow = findMountedRowNode(String(row && row.uid || ''));

            if (!mountedRow) {
                return;
            }

            mountedRow.classList.add('ns-live-runtime__mounted-row');
            mountedRow.appendChild(renderRowOverlayNode(row));
        });

        draftRows.forEach(function (row, index, rows) {
            var rowHost;
            var nextMountedRow;
            var container;

            rowHost = renderRowNode(row);
            nextMountedRow = findNextMountedRowNode(rows, index + 1);
            container = nextMountedRow && nextMountedRow.parentNode ? nextMountedRow.parentNode : scope;

            if (nextMountedRow && nextMountedRow.parentNode === container) {
                container.insertBefore(rowHost, nextMountedRow);
                return;
            }

            container.appendChild(rowHost);
        });
    }

    function ensureRowHasColumn(row) {
        if (!row || typeof row !== 'object') {
            return null;
        }

        if (!Array.isArray(row.columns)) {
            row.columns = [];
        }

        if (!row.columns.length) {
            row.columns.push({
                uid: makeUid('column'),
                title: 'Колонка 1',
                kind: 'column',
                width: 12,
                hidden: false,
                meta: {},
                widgets: [],
                nested_rows: []
            });
        }

        return row.columns[0];
    }

    function insertBlockIntoLayout(widgetToInsert) {
        var record = findNodeRecord(layoutState.desktop.rows, selectedTargetKey);
        var widgets;
        var insertIndex;
        var column;

        if (!record) {
            return false;
        }

        if (record.kind === 'widget') {
            column = record.column;
            widgets = Array.isArray(column.widgets) ? column.widgets : [];
            insertIndex = widgets.findIndex(function (widget) {
                return String(widget.uid || '') === String(record.node.uid || '');
            });

            if (insertIndex === -1) {
                widgets.push(widgetToInsert);
            } else {
                widgets.splice(insertIndex + 1, 0, widgetToInsert);
            }

            column.widgets = widgets;
            return true;
        }

        if (record.kind === 'column') {
            if (!Array.isArray(record.column.widgets)) {
                record.column.widgets = [];
            }
            record.column.widgets.push(widgetToInsert);
            return true;
        }

        if (record.kind === 'row') {
            column = ensureRowHasColumn(record.row);
            if (!Array.isArray(column.widgets)) {
                column.widgets = [];
            }
            column.widgets.push(widgetToInsert);
            return true;
        }

        return false;
    }

    function updateInsertContext() {
        var record = findNodeRecord(getRenderableRows(), selectedTargetKey);
        var text = 'Точка вставки: в конец страницы.';

        if (!overlayNodes.insertContext) {
            return;
        }

        if (record && record.kind === 'row') {
            text = 'Точка вставки: сразу после секции «' + record.title + '».';
        } else if (record && record.kind === 'column') {
            text = 'Точка вставки: внутрь колонки «' + record.title + '».';
        } else if (record && record.kind === 'widget') {
            text = 'Точка вставки: сразу после блока «' + record.title + '» в колонке «' + String((record.column && record.column.title) || 'Колонка') + '».';
        } else if (selectedTitle) {
            text = 'Точка вставки: текущий DOM-элемент вне draft-layout, поэтому новая секция будет добавлена в конец страницы.';
        }

        overlayNodes.insertContext.textContent = text;
    }

    function setContentHint(text) {
        if (overlayNodes.contentHint) {
            overlayNodes.contentHint.textContent = text;
        }
    }

    function setContentPanelVisible(isVisible) {
        if (overlayNodes.contentForm) {
            overlayNodes.contentForm.hidden = !isVisible;
        }
    }

    function setInstantMoveHint(text) {
        if (overlayNodes.instantMoveHint) {
            overlayNodes.instantMoveHint.textContent = text;
        }
    }

    function setInstantMovePanelVisible(isVisible) {
        if (overlayNodes.instantMovePanel) {
            overlayNodes.instantMovePanel.hidden = !isVisible;
        }
    }

    function setInstantMoveBusy(isBusy, buttonText) {
        if (overlayNodes.instantMoveButton) {
            overlayNodes.instantMoveButton.disabled = Boolean(isBusy);
            overlayNodes.instantMoveButton.textContent = buttonText || 'Переместить блок';
        }
        if (overlayNodes.instantMoveSelect) {
            overlayNodes.instantMoveSelect.disabled = Boolean(isBusy);
        }
    }

    function clearInstantDropStates() {
        Array.prototype.slice.call(doc.querySelectorAll('.ns-live-runtime__column--instant-target')).forEach(function (columnNode) {
            columnNode.classList.remove('is-drop-enabled');
            columnNode.classList.remove('is-drop-current');
            columnNode.classList.remove('is-drop-hovered');
        });

        Array.prototype.slice.call(doc.querySelectorAll('.ns-live-runtime__block--draggable.is-dragging')).forEach(function (widgetNode) {
            widgetNode.classList.remove('is-dragging');
        });
    }

    function markInstantDropTargets(isActive) {
        Array.prototype.slice.call(doc.querySelectorAll('.ns-live-runtime__column--instant-target')).forEach(function (columnNode) {
            var positionName = String(columnNode.getAttribute('data-instant-position') || '').trim();

            columnNode.classList.remove('is-drop-enabled');
            columnNode.classList.remove('is-drop-current');
            columnNode.classList.remove('is-drop-hovered');

            if (!isActive || positionName === '') {
                return;
            }

            columnNode.classList.add('is-drop-enabled');

            if (positionName === instantDragState.sourcePosition) {
                columnNode.classList.add('is-drop-current');
            }
        });
    }

    function setInstantDropHover(columnNode) {
        var hoverPosition = '';

        Array.prototype.slice.call(doc.querySelectorAll('.ns-live-runtime__column--instant-target.is-drop-hovered')).forEach(function (node) {
            if (node !== columnNode) {
                node.classList.remove('is-drop-hovered');
            }
        });

        if (columnNode) {
            hoverPosition = String(columnNode.getAttribute('data-instant-position') || '').trim();
            columnNode.classList.add('is-drop-hovered');

            if (overlayNodes.instantMoveSelect && hoverPosition !== '') {
                overlayNodes.instantMoveSelect.value = hoverPosition;
            }
        }

        instantDragState.hoverPosition = hoverPosition;
    }

    function resetInstantDragState() {
        instantDragState.active = false;
        instantDragState.widgetUid = '';
        instantDragState.bindingPageId = 0;
        instantDragState.sourcePosition = '';
        instantDragState.hoverPosition = '';
        clearInstantDropStates();
    }

    function getInstantDropColumnNode(target) {
        var element = target && target.nodeType === 1 ? target : (target && target.parentElement ? target.parentElement : null);

        if (!element || !element.closest) {
            return null;
        }

        return element.closest('.ns-live-runtime__column--instant-target[data-instant-position]');
    }

    function beginInstantWidgetDrag(widgetNode, event) {
        var bindingPageId;
        var sourcePosition;
        var widgetUid;

        if (!isLayoutMode() || !widgetNode) {
            return false;
        }

        bindingPageId = parseInt(widgetNode.getAttribute('data-instant-binding-page-id') || '0', 10) || 0;
        sourcePosition = String(widgetNode.getAttribute('data-instant-position') || '').trim();
        widgetUid = String(widgetNode.getAttribute('data-nordic-id') || '').trim();

        if (bindingPageId < 1 || widgetUid === '') {
            return false;
        }

        selectElement(widgetNode);

        instantDragState.active = true;
        instantDragState.widgetUid = widgetUid;
        instantDragState.bindingPageId = bindingPageId;
        instantDragState.sourcePosition = sourcePosition;
        instantDragState.hoverPosition = '';

        widgetNode.classList.add('is-dragging');
        markInstantDropTargets(true);

        if (overlayNodes.instantMoveSelect && sourcePosition !== '') {
            overlayNodes.instantMoveSelect.value = sourcePosition;
        }

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', widgetUid);
        }

        setInstantMoveHint('Перетащите карточку блока в нужную live-колонку. Сохранение пойдет прямо в widgets_bind_pages.');
        setStatus('Перетащите блок в другую Instant position прямо на странице.', 'muted');

        return true;
    }

    function setWidgetOptionsHint(text) {
        if (overlayNodes.widgetOptionsHint) {
            overlayNodes.widgetOptionsHint.textContent = text;
        }
    }

    function setWidgetOptionsPanelVisible(isVisible) {
        if (overlayNodes.widgetOptionsPanel) {
            overlayNodes.widgetOptionsPanel.hidden = !isVisible;
        }
    }

    function setWidgetOptionsBusy(isBusy, buttonText) {
        if (overlayNodes.widgetOptionsSaveButton) {
            overlayNodes.widgetOptionsSaveButton.disabled = Boolean(isBusy);
            overlayNodes.widgetOptionsSaveButton.textContent = buttonText || 'Сохранить настройки блока';
        }
    }

    function getSelectedWidgetContext() {
        var record = findNodeRecord(getDraftRows(), selectedTargetKey);

        if (!record || record.kind !== 'widget' || !record.node || typeof record.node !== 'object') {
            return null;
        }

        return {
            record: record,
            widget: record.node
        };
    }

    function getWidgetOptionsTemplateName() {
        return String(pageInfo.template || '').trim();
    }

    function clearWidgetOptionsForm() {
        if (overlayNodes.widgetOptionsFormHost) {
            overlayNodes.widgetOptionsFormHost.innerHTML = '';
        }

        if (overlayNodes.widgetOptionsPanel) {
            overlayNodes.widgetOptionsPanel.removeAttribute('data-target-key');
        }
    }

    function renderWidgetOptionsForm(html) {
        var form;

        clearWidgetOptionsForm();

        if (!overlayNodes.widgetOptionsFormHost) {
            return;
        }

        form = doc.createElement('form');
        form.className = 'ns-live-editor__widget-form';
        form.innerHTML = html || '';
        overlayNodes.widgetOptionsFormHost.appendChild(form);
    }

    function extractErrorMessage(payload) {
        var message = '';

        if (typeof payload === 'string') {
            return payload;
        }

        if (Array.isArray(payload)) {
            payload.some(function (item) {
                message = extractErrorMessage(item);
                return Boolean(message);
            });
            return message;
        }

        if (!payload || typeof payload !== 'object') {
            return '';
        }

        Object.keys(payload).some(function (key) {
            message = extractErrorMessage(payload[key]);
            return Boolean(message);
        });

        return message;
    }

    function refreshSelectedDraftNode() {
        var targetNode;

        if (!selectedTargetKey) {
            return;
        }

        targetNode = doc.querySelector(buildNodeSelector(selectedTargetKey));
        if (!targetNode) {
            syncMeta();
            updateInsertContext();
            updateContentInspector();
            updateWidgetOptionsInspector();
            return;
        }

        selectedElement = targetNode;
        positionOutline(targetNode);
        syncMeta();
        updateInsertContext();
        updateContentInspector();
        updateInstantMoveInspector();
        updateWidgetOptionsInspector();
    }

    function getSelectedInstantWidgetContext() {
        var record = findNodeRecord(getInstantRows(), selectedTargetKey);

        if (!record || record.kind !== 'widget' || !record.node || typeof record.node !== 'object') {
            return null;
        }

        return {
            record: record,
            widget: record.node
        };
    }

    function collectInstantMoveTargets(rows, result) {
        (Array.isArray(rows) ? rows : []).forEach(function (row) {
            (Array.isArray(row.columns) ? row.columns : []).forEach(function (column) {
                var meta = column && column.meta && typeof column.meta === 'object' ? column.meta : {};
                var positionName = String(meta.position_name || '').trim();

                if (positionName !== '') {
                    result.push({
                        value: positionName,
                        label: String(row.title || 'Секция') + ' -> ' + String(column.title || positionName)
                    });
                }

                collectInstantMoveTargets(column.nested_rows || [], result);
            });
        });

        return result;
    }

    function updateInstantMoveInspector() {
        var context;
        var targets = [];
        var currentPosition;

        if (!overlayNodes.instantMovePanel || !overlayNodes.instantMoveSelect) {
            return;
        }

        context = getSelectedInstantWidgetContext();

        if (!context || !context.widget || !context.widget.binding_page_id) {
            overlayNodes.instantMoveSelect.innerHTML = '';
            setInstantMovePanelVisible(false);
            setInstantMoveHint('Выберите реальный Instant block, чтобы переместить его между positions ядра или перетащите его карточку по live overlay.');
            return;
        }

        currentPosition = String(context.widget.position_name || '').trim();
        collectInstantMoveTargets(getInstantRows(), targets);
        targets = targets.filter(function (target, index, items) {
            return target.value !== '' && items.findIndex(function (item) {
                return item.value === target.value;
            }) === index;
        });

        overlayNodes.instantMoveSelect.innerHTML = '';
        targets.forEach(function (target) {
            var option = doc.createElement('option');
            option.value = target.value;
            option.textContent = target.label + (target.value === currentPosition ? ' (текущая)' : '');
            option.selected = target.value === currentPosition;
            overlayNodes.instantMoveSelect.appendChild(option);
        });

        setInstantMovePanelVisible(targets.length > 0);
        setInstantMoveHint('Source of truth: widgets_bind_pages. Можно выбрать position ниже или перетащить карточку блока прямо в live-колонку.');
    }

    function loadInstantScheme() {
        if (!isLayoutMode() || !instantSchemeUrl || !window.fetch || !config.csrf_token || instantAdapterState.loading) {
            return Promise.resolve();
        }

        instantAdapterState.loading = true;

        return window.fetch(instantSchemeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeBody({
                csrf_token: config.csrf_token,
                template: String(pageInfo.template || '').trim(),
                uri: String(pageInfo.uri_raw || '/').trim() || '/'
            })
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                throw response || new Error('instant-scheme-load-failed');
            }

            instantAdapterState.rows = normalizeInstantRows(response.rows || []);
            instantAdapterState.loaded = true;
            renderLayoutState();
            refreshSelectedDraftNode();
        }).catch(function (error) {
            setStatus((error && error.message) || 'Не удалось загрузить Instant scheme adapter.', 'warn');
        }).finally(function () {
            instantAdapterState.loading = false;
        });
    }

    function triggerSelectedInstantMove(targetPosition, statusText) {
        var context = getSelectedInstantWidgetContext();
        var currentPosition;

        if (!context || !context.widget || !context.widget.binding_page_id) {
            setStatus('Сначала выберите реальный Instant block.', 'warn');
            return Promise.resolve(false);
        }

        if (targetPosition === '') {
            setStatus('Выберите целевую position для перемещения.', 'warn');
            return Promise.resolve(false);
        }

        currentPosition = String(context.widget.position_name || '').trim();
        if (currentPosition !== '' && currentPosition === targetPosition) {
            setStatus('Блок уже находится в этой Instant position.', 'warn');
            return Promise.resolve(false);
        }

        if (!instantMoveUrl || !window.fetch || !config.csrf_token) {
            setStatus('Перемещение блока сейчас недоступно.', 'warn');
            return Promise.resolve(false);
        }

        setInstantMoveBusy(true, 'Перемещаю...');
        setStatus(statusText || 'Перемещаю блок через Instant scheme...', 'muted');

        return window.fetch(instantMoveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeBody({
                csrf_token: config.csrf_token,
                binding_page_id: String(context.widget.binding_page_id || ''),
                target_position: targetPosition,
                template: String(pageInfo.template || '').trim()
            })
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                throw response || new Error('instant-move-failed');
            }

            setStatus(response.message || 'Блок перемещен.', 'ok');
            reloadOverlayPage(String(context.widget.uid || '').trim());
            return true;
        }).catch(function (error) {
            setStatus((error && error.message) || 'Не удалось переместить блок.', 'warn');
            return false;
        }).finally(function () {
            setInstantMoveBusy(false);
        });
    }

    function saveSelectedInstantMove() {
        var targetPosition = overlayNodes.instantMoveSelect ? String(overlayNodes.instantMoveSelect.value || '').trim() : '';

        return triggerSelectedInstantMove(targetPosition, 'Перемещаю блок через Instant scheme...');
    }

    function loadSelectedWidgetOptions(forceReload) {
        var context = getSelectedWidgetContext();

        if (!context || !isSystemWidget(context.widget)) {
            clearWidgetOptionsForm();
            setWidgetOptionsPanelVisible(false);
            setWidgetOptionsHint('Выберите system block, чтобы открыть его реальные настройки InstantCMS прямо в layout mode.');
            return;
        }

        setWidgetOptionsPanelVisible(true);

        if (!context.widget.has_options) {
            clearWidgetOptionsForm();
            setWidgetOptionsHint('У этого системного блока нет отдельной формы настроек. Публикация создаст правило с текущей bind-конфигурацией.');
            if (overlayNodes.widgetOptionsSaveButton) {
                overlayNodes.widgetOptionsSaveButton.hidden = true;
            }
            return;
        }

        if (!widgetOptionsUrl || !window.fetch || !config.csrf_token) {
            clearWidgetOptionsForm();
            setWidgetOptionsHint('Загрузка формы настроек сейчас недоступна.');
            if (overlayNodes.widgetOptionsSaveButton) {
                overlayNodes.widgetOptionsSaveButton.hidden = false;
            }
            return;
        }

        if (!forceReload && overlayNodes.widgetOptionsPanel && overlayNodes.widgetOptionsPanel.getAttribute('data-target-key') === String(context.widget.uid || '')) {
            setWidgetOptionsHint('Редактируйте реальные поля виджета и сохраняйте их в draft-layout.');
            if (overlayNodes.widgetOptionsSaveButton) {
                overlayNodes.widgetOptionsSaveButton.hidden = false;
            }
            return;
        }

        setWidgetOptionsBusy(true, 'Загружаю форму...');
        setWidgetOptionsHint('Загружаю реальные настройки виджета из InstantCMS...');

        window.fetch(widgetOptionsUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeBody({
                csrf_token: config.csrf_token,
                widget_id: String(context.widget.widget_id || ''),
                bind_id: String(context.widget.bind_id || ''),
                template: getWidgetOptionsTemplateName(),
                options: JSON.stringify(getWidgetBindConfig(context.widget))
            })
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                clearWidgetOptionsForm();
                setWidgetOptionsHint(response && response.message ? response.message : 'Не удалось загрузить форму настроек виджета.');
                return;
            }

            if (overlayNodes.widgetOptionsPanel) {
                overlayNodes.widgetOptionsPanel.setAttribute('data-target-key', String(context.widget.uid || ''));
            }

            renderWidgetOptionsForm(response.html || '');
            setWidgetOptionsHint('Редактируйте реальные поля виджета и сохраняйте их в draft-layout.');
            if (overlayNodes.widgetOptionsSaveButton) {
                overlayNodes.widgetOptionsSaveButton.hidden = false;
            }
        }).catch(function () {
            clearWidgetOptionsForm();
            setWidgetOptionsHint('Не удалось загрузить форму настроек виджета.');
        }).finally(function () {
            setWidgetOptionsBusy(false);
        });
    }

    function updateWidgetOptionsInspector() {
        if (!overlayNodes.widgetOptionsPanel) {
            return;
        }

        loadSelectedWidgetOptions(false);
    }

    function saveSelectedWidgetOptions() {
        var context = getSelectedWidgetContext();
        var form;
        var params;
        var snapshot;

        if (!context || !isSystemWidget(context.widget)) {
            setStatus('Сначала выберите system block внутри draft-layout.', 'warn');
            return;
        }

        if (!context.widget.has_options) {
            setStatus('У выбранного блока нет отдельной формы настроек.', 'warn');
            return;
        }

        form = overlayNodes.widgetOptionsFormHost ? overlayNodes.widgetOptionsFormHost.querySelector('form') : null;
        if (!form) {
            setStatus('Сначала дождитесь загрузки формы настроек блока.', 'warn');
            return;
        }

        if (!widgetOptionsSaveUrl || !window.fetch || !config.csrf_token) {
            setStatus('Сохранение настроек блока сейчас недоступно.', 'warn');
            return;
        }

        params = new URLSearchParams(new window.FormData(form));
        params.append('csrf_token', config.csrf_token);
        params.append('widget_id', String(context.widget.widget_id || ''));
        params.append('bind_id', String(context.widget.bind_id || ''));
        params.append('template', getWidgetOptionsTemplateName());
        params.append('bind_config', JSON.stringify(getWidgetBindConfig(context.widget)));

        setWidgetOptionsBusy(true, 'Сохраняю настройки...');
        setWidgetOptionsHint('Проверяю и подготавливаю bind-config через штатный form parser InstantCMS...');
        setStatus('Сохраняю настройки system block в draft-layout...', 'muted');

        window.fetch(widgetOptionsSaveUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error || !response.bind_config) {
                throw response || new Error('widget-options-save-failed');
            }

            snapshot = deepClone(layoutState);
            context.widget.bind_config = response.bind_config;
            context.widget.title = String(response.title || response.bind_config.title || context.widget.title || 'Виджет').trim() || 'Виджет';
            renderLayoutState();
            refreshSelectedDraftNode();
            setWidgetOptionsHint('Настройки применены локально. Сохраняю обновленный draft-layout...');

            return persistLayoutState().then(function (saveResponse) {
                if (!saveResponse || saveResponse.error) {
                    layoutState = normalizeLayoutState(snapshot);
                    renderLayoutState();
                    refreshSelectedDraftNode();
                    throw saveResponse || new Error('draft-layout-save-failed');
                }

                setWidgetOptionsHint('Настройки блока сохранены в draft-layout.');
                setStatus('Настройки блока сохранены.', 'ok');
            }).catch(function (saveError) {
                throw {
                    error: true,
                    message: (saveError && saveError.message) ? saveError.message : 'Не удалось сохранить обновленный draft-layout.'
                };
            });
        }).catch(function (error) {
            var message = (error && error.message) || extractErrorMessage(error && error.errors) || 'Не удалось сохранить настройки блока.';
            setWidgetOptionsHint(message);
            setStatus(message, 'warn');
        }).finally(function () {
            setWidgetOptionsBusy(false);
        });
    }

    function setContentFieldVisibility(fieldName, isVisible) {
        var node = panel ? panel.querySelector('[data-overlay-content-wrap="' + fieldName + '"]') : null;

        if (node) {
            node.hidden = !isVisible;
        }
    }

    function getSelectedPresetContext() {
        var record = findNodeRecord(getDraftRows(), selectedTargetKey);
        var row;
        var rowMeta;
        var presetKey;

        if (!record || !record.row) {
            return null;
        }

        row = record.row;
        rowMeta = row.meta && typeof row.meta === 'object' ? row.meta : {};
        presetKey = String(rowMeta.preset_key || '').trim();

        if (!presetKey) {
            return null;
        }

        return {
            record: record,
            row: row,
            rowMeta: rowMeta,
            presetKey: presetKey,
            content: normalizePresetContent(presetKey, rowMeta.content || {}, row.title || presetKey)
        };
    }

    function applyContentForm(context) {
        var visibleFields = {
            section_title: true,
            eyebrow: true,
            heading: true,
            text: true,
            items: ['features', 'cards', 'faq'].indexOf(context.presetKey) !== -1,
            button_text: ['hero', 'cta'].indexOf(context.presetKey) !== -1,
            button_url: ['hero', 'cta'].indexOf(context.presetKey) !== -1,
            html: context.presetKey === 'html'
        };

        if (contentFields.section_title) {
            contentFields.section_title.value = String(context.row.title || '').trim();
        }

        if (contentFields.eyebrow) {
            contentFields.eyebrow.value = context.content.eyebrow;
        }

        if (contentFields.heading) {
            contentFields.heading.value = context.content.heading;
        }

        if (contentFields.text) {
            contentFields.text.value = context.content.text;
        }

        if (contentFields.items) {
            contentFields.items.value = context.content.items.join('\n');
        }

        if (contentFields.button_text) {
            contentFields.button_text.value = context.content.button_text;
        }

        if (contentFields.button_url) {
            contentFields.button_url.value = context.content.button_url;
        }

        if (contentFields.html) {
            contentFields.html.value = context.content.html;
        }

        Object.keys(visibleFields).forEach(function (fieldName) {
            setContentFieldVisibility(fieldName, visibleFields[fieldName]);
        });

        setContentHint('Выбрана секция «' + (context.row.title || context.presetKey) + '». Меняйте текст ниже и сохраняйте в тот же draft-layout.');
        setContentPanelVisible(true);
    }

    function updateContentInspector() {
        var context = getSelectedPresetContext();

        if (!overlayNodes.contentForm) {
            return;
        }

        if (!context) {
            setContentHint('Выберите вставленную draft-секцию, чтобы сразу поменять ее текст прямо на странице.');
            setContentPanelVisible(false);
            return;
        }

        applyContentForm(context);
    }

    function buildContentPayload() {
        return {
            eyebrow: contentFields.eyebrow ? String(contentFields.eyebrow.value || '').trim() : '',
            heading: contentFields.heading ? String(contentFields.heading.value || '').trim() : '',
            text: contentFields.text ? String(contentFields.text.value || '').trim() : '',
            button_text: contentFields.button_text ? String(contentFields.button_text.value || '').trim() : '',
            button_url: contentFields.button_url ? String(contentFields.button_url.value || '').trim() : '',
            items: contentFields.items ? normalizeItems(String(contentFields.items.value || '')) : [],
            html: contentFields.html ? String(contentFields.html.value || '').trim() : ''
        };
    }

    function tryInsertAfterRow(rows, targetKey, rowToInsert) {
        var inserted = false;

        if (!Array.isArray(rows)) {
            return false;
        }

        rows.some(function (row, index) {
            if (!row || typeof row !== 'object') {
                return false;
            }

            if (String(row.uid || '') === targetKey) {
                rows.splice(index + 1, 0, rowToInsert);
                inserted = true;
                return true;
            }

            return (Array.isArray(row.columns) ? row.columns : []).some(function (column) {
                inserted = tryInsertAfterRow(column.nested_rows || [], targetKey, rowToInsert);
                return inserted;
            });
        });

        return inserted;
    }

    function tryInsertIntoColumn(rows, targetKey, rowToInsert) {
        var inserted = false;

        if (!Array.isArray(rows)) {
            return false;
        }

        rows.some(function (row) {
            return (Array.isArray(row.columns) ? row.columns : []).some(function (column) {
                if (String(column.uid || '') === targetKey) {
                    if (!Array.isArray(column.nested_rows)) {
                        column.nested_rows = [];
                    }

                    column.nested_rows.push(rowToInsert);
                    inserted = true;
                    return true;
                }

                inserted = tryInsertIntoColumn(column.nested_rows || [], targetKey, rowToInsert);

                return inserted;
            });
        });

        return inserted;
    }

    function insertPresetRow(rowToInsert) {
        var rootRows = layoutState.desktop.rows;

        if (selectedTargetKey && tryInsertAfterRow(rootRows, selectedTargetKey, rowToInsert)) {
            return;
        }

        if (selectedTargetKey && tryInsertIntoColumn(rootRows, selectedTargetKey, rowToInsert)) {
            return;
        }

        rootRows.push(rowToInsert);
    }

    function persistLayoutState() {
        var templateName = String(pageInfo.template || '').trim();
        var targetUri = String(pageInfo.uri_raw || window.location.pathname || '/').trim() || '/';

        if (!saveStateUrl || !window.fetch || !config.csrf_token || !templateName) {
            return Promise.reject(new Error('overlay-draft-save-unavailable'));
        }

        return window.fetch(saveStateUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeBody({
                csrf_token: config.csrf_token,
                template: templateName,
                uri: targetUri,
                layout_state: JSON.stringify(layoutState)
            })
        }).then(function (response) {
            return response.json();
        });
    }

    function updateResizeTooltip(leftWidth, rightWidth) {
        if (!resizeState.tooltip) {
            return;
        }

        resizeState.tooltip.textContent = formatColumnPercent(leftWidth) + ' / ' + formatColumnPercent(rightWidth);
    }

    function stopResizePreview() {
        if (resizeState.tooltip && resizeState.tooltip.parentNode) {
            resizeState.tooltip.parentNode.removeChild(resizeState.tooltip);
        }

        if (resizeState.handle) {
            resizeState.handle.classList.remove('is-dragging');
        }

        if (body) {
            body.classList.remove('ns-live-runtime--resizing');
        }

        resizeState.tooltip = null;
        resizeState.handle = null;
        resizeState.container = null;
    }

    function resetResizeState() {
        resizeState.active = false;
        resizeState.rowUid = '';
        resizeState.leftColumnUid = '';
        resizeState.rightColumnUid = '';
        resizeState.startX = 0;
        resizeState.startLeftWidth = 0;
        resizeState.startRightWidth = 0;
        resizeState.snapshot = null;
        stopResizePreview();
    }

    function applyResizePreview(leftWidth, rightWidth) {
        var leftNode;
        var rightNode;

        leftNode = doc.querySelector('[data-nordic-id="' + resizeState.leftColumnUid + '"]');
        rightNode = doc.querySelector('[data-nordic-id="' + resizeState.rightColumnUid + '"]');

        if (leftNode) {
            leftNode.style.setProperty('--ns-col-span', String(leftWidth));
            leftNode.setAttribute('data-column-width', formatColumnPercent(leftWidth));
            if (leftNode.querySelector('.ns-live-runtime__width-badge')) {
                leftNode.querySelector('.ns-live-runtime__width-badge').textContent = formatColumnPercent(leftWidth);
            }
        }

        if (rightNode) {
            rightNode.style.setProperty('--ns-col-span', String(rightWidth));
            rightNode.setAttribute('data-column-width', formatColumnPercent(rightWidth));
            if (rightNode.querySelector('.ns-live-runtime__width-badge')) {
                rightNode.querySelector('.ns-live-runtime__width-badge').textContent = formatColumnPercent(rightWidth);
            }
        }

        updateResizeTooltip(leftWidth, rightWidth);
    }

    function commitColumnResize(leftWidth, rightWidth) {
        var boundary = findRowColumnsByBoundary(resizeState.rowUid, resizeState.leftColumnUid, resizeState.rightColumnUid);

        if (!boundary) {
            resetResizeState();
            return;
        }

        boundary.leftColumn.width = leftWidth;
        boundary.rightColumn.width = rightWidth;

        setStatus('Сохраняю новую ширину колонок...', 'muted');

        persistLayoutState().then(function (response) {
            if (!response || response.error) {
                layoutState = normalizeLayoutState(resizeState.snapshot || {});
                renderLayoutState();
                refreshSelectedDraftNode();
                setStatus(response && response.message ? response.message : 'Не удалось сохранить ширину колонок.', 'warn');
                resetResizeState();
                return;
            }

            renderLayoutState();
            refreshSelectedDraftNode();
            setStatus('Ширина колонок сохранена.', 'ok');
            resetResizeState();
        }).catch(function () {
            layoutState = normalizeLayoutState(resizeState.snapshot || {});
            renderLayoutState();
            refreshSelectedDraftNode();
            setStatus('Сохранение ширины колонок сейчас недоступно.', 'warn');
            resetResizeState();
        });
    }

    function beginColumnResize(handle, event) {
        var boundary;

        if (!isLayoutMode()) {
            return;
        }

        boundary = findRowColumnsByBoundary(
            String(handle.getAttribute('data-resize-row') || ''),
            String(handle.getAttribute('data-resize-left') || ''),
            String(handle.getAttribute('data-resize-right') || '')
        );

        if (!boundary) {
            return;
        }

        resizeState.active = true;
        resizeState.rowUid = String(handle.getAttribute('data-resize-row') || '');
        resizeState.leftColumnUid = String(handle.getAttribute('data-resize-left') || '');
        resizeState.rightColumnUid = String(handle.getAttribute('data-resize-right') || '');
        resizeState.startX = event.clientX;
        resizeState.startLeftWidth = getColumnWidth(boundary.leftColumn);
        resizeState.startRightWidth = getColumnWidth(boundary.rightColumn);
        resizeState.container = handle.parentNode;
        resizeState.handle = handle;
        resizeState.snapshot = deepClone(layoutState);

        resizeState.tooltip = doc.createElement('div');
        resizeState.tooltip.className = 'ns-live-runtime__resize-tooltip';
        resizeState.tooltip.textContent = formatColumnPercent(resizeState.startLeftWidth) + ' / ' + formatColumnPercent(resizeState.startRightWidth);
        handle.appendChild(resizeState.tooltip);
        handle.classList.add('is-dragging');
        body.classList.add('ns-live-runtime--resizing');

        event.preventDefault();
        event.stopPropagation();
    }

    function updateColumnResize(event) {
        var containerWidth;
        var unitPx;
        var deltaColumns;
        var totalWidth;
        var nextLeftWidth;
        var nextRightWidth;

        if (!resizeState.active || !resizeState.container) {
            return;
        }

        containerWidth = resizeState.container.getBoundingClientRect().width;
        if (!containerWidth) {
            return;
        }

        unitPx = containerWidth / GRID_TOTAL_COLUMNS;
        deltaColumns = Math.round((event.clientX - resizeState.startX) / unitPx);
        totalWidth = resizeState.startLeftWidth + resizeState.startRightWidth;
        nextLeftWidth = clamp(resizeState.startLeftWidth + deltaColumns, 1, totalWidth - 1);
        nextRightWidth = totalWidth - nextLeftWidth;

        applyResizePreview(nextLeftWidth, nextRightWidth);
        resizeState.handle.setAttribute('data-next-left-width', String(nextLeftWidth));
        resizeState.handle.setAttribute('data-next-right-width', String(nextRightWidth));
    }

    function finishColumnResize() {
        var nextLeftWidth;
        var nextRightWidth;

        if (!resizeState.active) {
            return;
        }

        nextLeftWidth = parseInt(resizeState.handle && resizeState.handle.getAttribute('data-next-left-width') || resizeState.startLeftWidth, 10) || resizeState.startLeftWidth;
        nextRightWidth = parseInt(resizeState.handle && resizeState.handle.getAttribute('data-next-right-width') || resizeState.startRightWidth, 10) || resizeState.startRightWidth;

        if (resizeState.handle) {
            resizeState.handle.removeAttribute('data-next-left-width');
            resizeState.handle.removeAttribute('data-next-right-width');
        }

        resizeState.ignoreClickUntil = Date.now() + 400;

        if (nextLeftWidth === resizeState.startLeftWidth && nextRightWidth === resizeState.startRightWidth) {
            resetResizeState();
            return;
        }

        commitColumnResize(nextLeftWidth, nextRightWidth);
    }

    function buildModeUrl(baseUrl, focusKey) {
        var url;

        if (!baseUrl) {
            return '#';
        }

        try {
            url = new URL(baseUrl, window.location.origin);
            if (focusKey) {
                url.searchParams.set('nordicstyl_focus', focusKey);
            } else {
                url.searchParams.delete('nordicstyl_focus');
            }

            return url.toString();
        } catch (error) {
            return baseUrl;
        }
    }

    function syncModeLinks() {
        var focusKey = selectedTargetKey || getRequestedFocusKey();
        var designLink = panel ? panel.querySelector('[data-live-editor-mode-link="design"]') : null;
        var layoutLink = panel ? panel.querySelector('[data-live-editor-mode-link="layout"]') : null;

        if (designLink) {
            designLink.href = buildModeUrl(config.design_url || '', focusKey);
        }

        if (layoutLink) {
            layoutLink.href = buildModeUrl(config.layout_url || '', focusKey);
        }

        if (overlayNodes.handoffButton) {
            overlayNodes.handoffButton.href = buildModeUrl(config.design_url || '', focusKey);
        }
    }

    function reloadOverlayPage(focusKey) {
        var url = new URL(window.location.href);

        if (focusKey) {
            url.searchParams.set('nordicstyl_focus', focusKey);
        } else {
            url.searchParams.delete('nordicstyl_focus');
        }

        url.searchParams.set('nordicstyl_reload', String(Date.now()));
        window.location.assign(url.toString());
    }

    function insertPreset(presetKey, presetTitle) {
        var snapshot = deepClone(layoutState);
        var rowToInsert;

        if (!presetKey) {
            return;
        }

        rowToInsert = createPresetRow(presetKey, presetTitle);
        insertPresetRow(rowToInsert);
    renderLayoutState();
        updateInsertContext();
        setStatus('Сохраняю draft-layout для новой секции…', 'muted');

        persistLayoutState().then(function (response) {
            if (!response || response.error) {
                layoutState = normalizeLayoutState(snapshot);
                updateInsertContext();
                updateContentInspector();
                setStatus(response && response.message ? response.message : 'Не удалось сохранить draft-layout.', 'warn');
                return;
            }

            selectedTargetKey = String(rowToInsert.uid || '');
            selectedTitle = String(rowToInsert.title || presetTitle || 'Секция');
            selectedSelector = buildNodeSelector(selectedTargetKey);
            syncModeLinks();
            setStatus('Секция добавлена. Перезагружаю страницу и ставлю фокус на новый блок.', 'ok');
            reloadOverlayPage(selectedTargetKey);
        }).catch(function () {
            layoutState = normalizeLayoutState(snapshot);
            renderLayoutState();
            updateInsertContext();
            updateContentInspector();
            setStatus('Сохранение draft-layout сейчас недоступно.', 'warn');
        });
    }

    function saveSelectedPresetContent() {
        var context = getSelectedPresetContext();
        var snapshot = deepClone(layoutState);
        var nextTitle;

        if (!context) {
            setStatus('Сначала выберите вставленную draft-секцию.', 'warn');
            return;
        }

        nextTitle = contentFields.section_title ? String(contentFields.section_title.value || '').trim() : '';
        context.row.title = nextTitle || String(context.row.title || context.presetKey || 'Секция');

        if (!context.row.meta || typeof context.row.meta !== 'object') {
            context.row.meta = {};
        }

        context.row.meta.preset_key = context.presetKey;
        context.row.meta.content = buildContentPayload();
        renderLayoutState();

        selectedTargetKey = String(context.row.uid || '').trim();
        selectedTitle = String(context.row.title || '').trim();
        selectedSelector = buildNodeSelector(selectedTargetKey);
        syncMeta();
        updateInsertContext();
        updateContentInspector();
        setStatus('Сохраняю контент draft-секции…', 'muted');

        persistLayoutState().then(function (response) {
            if (!response || response.error) {
                layoutState = normalizeLayoutState(snapshot);
                renderLayoutState();
                updateInsertContext();
                updateContentInspector();
                setStatus(response && response.message ? response.message : 'Не удалось сохранить контент draft-секции.', 'warn');
                return;
            }

            setStatus('Контент секции сохранен. Перезагружаю страницу.', 'ok');
            reloadOverlayPage(selectedTargetKey);
        }).catch(function () {
            layoutState = normalizeLayoutState(snapshot);
            renderLayoutState();
            updateInsertContext();
            updateContentInspector();
            setStatus('Сохранение контента сейчас недоступно.', 'warn');
        });
    }

    function insertBlock(block) {
        var snapshot = deepClone(layoutState);

        if (!selectedTargetKey) {
            setStatus('Сначала выберите секцию, колонку или существующий блок.', 'warn');
            return;
        }

        if (!insertBlockIntoLayout(block)) {
            setStatus('Для вставки блока сначала кликните по draft-секции внутри основной контентной зоны.', 'warn');
            return;
        }

        renderLayoutState();
        updateInsertContext();
        setStatus('Сохраняю новый блок в draft-layout…', 'muted');

        persistLayoutState().then(function (response) {
            if (!response || response.error) {
                layoutState = normalizeLayoutState(snapshot);
                renderLayoutState();
                updateInsertContext();
                setStatus(response && response.message ? response.message : 'Не удалось сохранить новый блок.', 'warn');
                return;
            }

            selectedTargetKey = String(block.uid || '').trim();
            selectedTitle = String(block.title || 'Блок').trim() || 'Блок';
            selectedSelector = buildNodeSelector(selectedTargetKey);
            syncMeta();
            syncModeLinks();
            setStatus('Блок добавлен. Перезагружаю страницу и сохраняю фокус на нем.', 'ok');
            reloadOverlayPage(selectedTargetKey);
        }).catch(function () {
            layoutState = normalizeLayoutState(snapshot);
            renderLayoutState();
            updateInsertContext();
            setStatus('Сохранение блока сейчас недоступно.', 'warn');
        });
    }

    function ensureOverlay() {
        if (overlay) {
            return overlay;
        }

        overlay = doc.createElement('div');
        overlay.className = 'ns-live-overlay__outline';
        overlay.setAttribute('data-nordicstyl-picker-overlay', '1');
        body.appendChild(overlay);

        return overlay;
    }

    function ensurePreviewStyleNode() {
        if (previewStyleNode) {
            return previewStyleNode;
        }

        previewStyleNode = doc.getElementById('nordicstyl-live-editor-preview');
        if (!previewStyleNode) {
            previewStyleNode = doc.createElement('style');
            previewStyleNode.id = 'nordicstyl-live-editor-preview';
            root.appendChild(previewStyleNode);
        }

        return previewStyleNode;
    }

    function positionOutline(element) {
        var node = ensureOverlay();
        var rect;

        if (!element || element.nodeType !== 1) {
            node.style.display = 'none';
            return;
        }

        rect = element.getBoundingClientRect();

        node.style.display = rect.width > 0 && rect.height > 0 ? 'block' : 'none';
        node.style.top = String(rect.top + window.scrollY) + 'px';
        node.style.left = String(rect.left + window.scrollX) + 'px';
        node.style.width = String(rect.width) + 'px';
        node.style.height = String(rect.height) + 'px';
    }

    function normalizeValue(propertyName, value) {
        value = String(value || '').trim();

        if (!value) {
            return '';
        }

        if (['font-size', 'padding', 'border-radius', 'border-width'].indexOf(propertyName) !== -1 && /^-?\d+(\.\d+)?$/.test(value)) {
            return value + 'px';
        }

        return value;
    }

    function collectDeclarations() {
        var declarations = {};

        Object.keys(fields).forEach(function (key) {
            var value = normalizeValue(key, fields[key].value);

            if (!value) {
                return;
            }

            declarations[key] = value;
        });

        if ((declarations['border-width'] || declarations['border-color']) && !declarations['border-style']) {
            declarations['border-style'] = 'solid';
        }

        return declarations;
    }

    function getBranch(device, state) {
        if (!currentRule.styles[device] || !currentRule.styles[device][state]) {
            return {};
        }

        return Object.assign({}, currentRule.styles[device][state]);
    }

    function getMergedDeclarations() {
        var baseDefault = getBranch('base', stateKey);

        if (deviceKey === 'base') {
            return baseDefault;
        }

        return Object.assign({}, baseDefault, getBranch(deviceKey, stateKey));
    }

    function setBranch(declarations) {
        if (!currentRule.styles[deviceKey]) {
            currentRule.styles[deviceKey] = {};
        }

        if (!Object.keys(declarations).length) {
            delete currentRule.styles[deviceKey][stateKey];
            if (!Object.keys(currentRule.styles[deviceKey]).length) {
                delete currentRule.styles[deviceKey];
            }
            return;
        }

        currentRule.styles[deviceKey][stateKey] = declarations;
    }

    function applyDeclarationsToFields(declarations) {
        Object.keys(fields).forEach(function (key) {
            fields[key].value = declarations[key] ? String(declarations[key]) : '';
        });
    }

    function selectorForState(selector, state) {
        var suffix = {
            hover: ':hover',
            active: ':active',
            focus: ':focus',
            'focus-visible': ':focus-visible'
        };

        return selector + (suffix[state] || '');
    }

    function wrapCss(device, css) {
        var mediaMap = {
            mobile: '@media (max-width: 767.98px)',
            tablet: '@media (min-width: 768px) and (max-width: 991.98px)',
            desktop: '@media (min-width: 992px)'
        };

        if (!css.trim()) {
            return '';
        }

        return mediaMap[device] ? (mediaMap[device] + '{' + css + '}') : css;
    }

    function buildPreviewCss() {
        var css = '';

        if (!currentRule.path) {
            return '';
        }

        ['base', 'mobile', 'tablet', 'desktop'].forEach(function (device) {
            var deviceStyles = currentRule.styles[device] || {};
            var deviceCss = '';

            Object.keys(deviceStyles).forEach(function (state) {
                var selector = selectorForState(currentRule.path, state);
                var declarations = deviceStyles[state];
                var block = '';

                Object.keys(declarations || {}).forEach(function (propertyName) {
                    var value = String(declarations[propertyName] || '').trim();
                    if (!value) {
                        return;
                    }
                    block += propertyName + ':' + value + ';';
                });

                if (block) {
                    deviceCss += selector + '{' + block + '}';
                }
            });

            css += wrapCss(device, deviceCss);
        });

        return css;
    }

    function applyPreview() {
        ensurePreviewStyleNode().textContent = buildPreviewCss();
    }

    function notifyParent(type, payload) {
        var message = payload && typeof payload === 'object' ? payload : {};

        message.type = type;

        if (window.parent === window) {
            return;
        }

        try {
            window.parent.postMessage(message, window.location.origin);
        } catch (error) {
            return;
        }
    }

    function setStatus(text, tone) {
        var statusNode = panel ? panel.querySelector('[data-live-editor-status]') : null;

        if (!statusNode) {
            return;
        }

        statusNode.textContent = text || '';
        statusNode.setAttribute('data-tone', tone || 'muted');

        notifyParent('nordicstyl-picker-status', {
            message: text || '',
            tone: tone || 'muted',
            selector: selectedSelector || '',
            storage_path: selectedTargetKey ? ('node:' + selectedTargetKey) : '',
            title: selectedTitle || '',
            source_label: selectedTargetSource || 'nordic id',
            target_key: selectedTargetKey || '',
            target_type: 'node',
            device: deviceKey,
            device_label: buildDeviceLabel(deviceKey),
            state: stateKey,
            state_label: buildStateLabel(stateKey),
            mode_label: buildDeviceLabel(deviceKey) + ' · ' + buildStateLabel(stateKey)
        });
    }

    function syncMeta() {
        var selectorNode = panel.querySelector('[data-live-editor-selector]');
        var titleNode = panel.querySelector('[data-live-editor-title]');
        var deviceNode = panel.querySelector('[data-live-editor-device-note]');

        selectorNode.textContent = selectedSelector || 'Пока не выбран';
        titleNode.textContent = selectedTitle || 'Кликните по элементу на странице';

        if (isLayoutMode()) {
            deviceNode.textContent = 'Режим layout: выберите место внутри контентного фрейма и добавьте секцию.';
        } else {
            deviceNode.textContent = deviceKey === 'base'
                ? 'Сейчас меняется ветка desktop для всех устройств.'
                : 'Сейчас меняется ветка ' + buildDeviceLabel(deviceKey) + ' для выбранного элемента.';
        }

        syncModeLinks();
        notifyContext();
    }

    function syncForm() {
        applyDeclarationsToFields(getMergedDeclarations());
        syncMeta();
    }

    function serializeBody(payload) {
        return Object.keys(payload).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(String(payload[key]));
        }).join('&');
    }

    function loadRuleForTarget(targetKey) {
        var selector = buildNodeSelector(targetKey);
        var requestToken = loadToken + 1;

        if (!isDesignMode()) {
            return;
        }

        loadToken = requestToken;
        currentRule = createEmptyRule(selector, targetKey);
        syncForm();
        applyPreview();

        if (!config.style_rule_url || !window.fetch || !config.csrf_token || !targetKey) {
            return;
        }

        window.fetch(config.style_rule_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeBody({
                csrf_token: config.csrf_token,
                mode: 'load',
                selector: selector,
                target_type: 'node',
                target_key: targetKey
            })
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (loadToken !== requestToken || targetKey !== selectedTargetKey) {
                return;
            }

            if (response && response.rule) {
                currentRule = {
                    id: parseInt(response.rule.id || 0, 10) || 0,
                    title: String(response.rule.title || ''),
                    path: String(response.rule.path || selector),
                    target_type: String(response.rule.target_type || 'node'),
                    target_key: String(response.rule.target_key || targetKey),
                    styles: response.rule.styles && typeof response.rule.styles === 'object' ? response.rule.styles : {},
                    custom: response.rule.custom && typeof response.rule.custom === 'object' ? response.rule.custom : {}
                };
                setStatus('Найдено существующее правило. Можно править сразу.', 'ok');
            } else {
                setStatus('Для этого элемента еще нет сохраненного правила. Первое сохранение создаст его.', 'muted');
            }

            syncForm();
            applyPreview();
        }).catch(function () {
            setStatus('Не удалось загрузить существующее правило. Можно продолжить и сохранить новое.', 'warn');
        });
    }

    function saveRule() {
        var payload;

        if (!isDesignMode()) {
            setStatus('Сохранение стилей доступно только в режиме design.', 'warn');
            return;
        }

        if (saveInFlight) {
            return;
        }

        if (!selectedTargetKey || !selectedSelector) {
            setStatus('Сначала выберите элемент на странице.', 'warn');
            return;
        }

        if (!config.style_rule_url || !window.fetch || !config.csrf_token) {
            setStatus('Сохранение сейчас недоступно.', 'warn');
            return;
        }

        setBranch(collectDeclarations());
        applyPreview();
        saveInFlight = true;
        setStatus('Сохраняю стиль на сайт...', 'muted');

        payload = {
            csrf_token: config.csrf_token,
            mode: 'save',
            selector: selectedSelector,
            target_type: 'node',
            target_key: selectedTargetKey,
            title: selectedTitle ? ('Live · ' + selectedTitle) : ('Live · ' + selectedSelector),
            rule_id: currentRule.id || 0,
            styles: JSON.stringify(currentRule.styles || {}),
            custom: JSON.stringify(currentRule.custom || {})
        };

        window.fetch(config.style_rule_url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeBody(payload)
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                setStatus((response && response.message) ? response.message : 'Не удалось сохранить правило.', 'warn');
                return;
            }

            if (response.rule) {
                currentRule.id = parseInt(response.rule.id || 0, 10) || currentRule.id;
            }

            setStatus(response.message || 'Стиль сохранен на сайт.', 'ok');
        }).catch(function () {
            setStatus('Сохранение сейчас недоступно.', 'warn');
        }).finally(function () {
            saveInFlight = false;
        });
    }

    function selectElement(element) {
        var target;

        if (!element || element === panel || panel.contains(element)) {
            return;
        }

        target = resolveElementTarget(element);

        if (!target.element || !target.targetKey || !target.selector) {
            setStatus('Этот элемент пока не размечен data-nordic-id и не редактируется.', 'warn');
            return;
        }

        selectedElement = target.element;
        selectedSelector = target.selector;
        selectedTargetKey = target.targetKey;
        selectedTitle = target.title;
        selectedTargetSource = target.sourceLabel;

        positionOutline(target.element);
        currentRule = createEmptyRule(selectedSelector, selectedTargetKey);
        syncMeta();
        updateInsertContext();
        updateContentInspector();
        updateInstantMoveInspector();
        updateWidgetOptionsInspector();

        if (isLayoutMode()) {
            setStatus('Точка вставки обновлена. Теперь можно добавить секцию или блок.', 'muted');
            return;
        }

        loadRuleForTarget(selectedTargetKey);

        try {
            notifyParent('nordicstyl-picker', {
                selector: selectedSelector,
                storage_path: selectedTargetKey ? ('node:' + selectedTargetKey) : '',
                target_key: selectedTargetKey,
                target_type: 'node',
                title: selectedTitle || '',
                source_label: selectedTargetSource || 'nordic id',
                device: deviceKey,
                device_label: buildDeviceLabel(deviceKey),
                state: stateKey,
                state_label: buildStateLabel(stateKey),
                mode_label: buildDeviceLabel(deviceKey) + ' · ' + buildStateLabel(stateKey)
            });
        } catch (error) {
            return;
        }
    }

    function buildOverlayContentControl(label, key, type, placeholder) {
        var row = doc.createElement('label');
        var title = doc.createElement('span');
        var input = type === 'textarea' ? doc.createElement('textarea') : doc.createElement('input');

        row.className = 'ns-live-editor__field';
        row.setAttribute('data-overlay-content-wrap', key);
        title.className = 'ns-live-editor__label';
        title.textContent = label;

        input.className = type === 'textarea' ? 'ns-live-editor__textarea' : 'ns-live-editor__input';

        if (type !== 'textarea') {
            input.type = 'text';
        } else {
            input.rows = key === 'html' ? 5 : 3;
        }

        input.placeholder = placeholder;
        row.appendChild(title);
        row.appendChild(input);
        contentFields[key] = input;

        return row;
    }

    function buildField(label, key, placeholder) {
        var row = doc.createElement('label');
        var title = doc.createElement('span');
        var input = doc.createElement('input');

        row.className = 'ns-live-editor__field';
        title.className = 'ns-live-editor__label';
        title.textContent = label;

        input.className = 'ns-live-editor__input';
        input.type = 'text';
        input.placeholder = placeholder;
        input.addEventListener('input', function () {
            setBranch(collectDeclarations());
            applyPreview();
        });

        row.appendChild(title);
        row.appendChild(input);
        fields[key] = input;

        return row;
    }

    function buildPanel() {
        var shell = doc.createElement('div');
        var header = doc.createElement('div');
        var titleWrap = doc.createElement('div');
        var title = doc.createElement('div');
        var subtitle = doc.createElement('div');
        var modeSwitch = null;
        var close = doc.createElement('button');
        var exitButton = doc.createElement('a');
        var meta = doc.createElement('div');
        var nameNode = doc.createElement('div');
        var selectorNode = doc.createElement('div');
        var switchers = doc.createElement('div');
        var deviceSelect = doc.createElement('select');
        var stateSelect = doc.createElement('select');
        var note = doc.createElement('div');
        var sectionsBlock = null;
        var blocksBlock = null;
        var insertContext = null;
        var presetList = null;
        var contentBlock = null;
        var contentHint = null;
        var contentForm = null;
        var contentSaveButton = null;
        var instantMoveBlock = null;
        var widgetOptionsBlock = null;
        var fieldsGrid = doc.createElement('div');
        var actions = doc.createElement('div');
        var saveButton = doc.createElement('button');
        var rulesButton = doc.createElement('a');
        var status = doc.createElement('div');

        shell.className = 'ns-live-editor';
    shell.setAttribute('data-builder-mode', builderMode);
        shell.innerHTML = '';

        header.className = 'ns-live-editor__header';
        titleWrap.className = 'ns-live-editor__title-wrap';
        title.className = 'ns-live-editor__title';
        title.textContent = config.is_overlay
            ? (isLayoutMode() ? 'Layout builder' : 'Design builder')
            : 'Live style';
        subtitle.className = 'ns-live-editor__subtitle';
        subtitle.textContent = config.is_overlay
            ? (isLayoutMode()
                ? 'Добавляйте секции только внутри контентного фрейма, не ломая общий шаблон страницы.'
                : 'Кликните по реальной странице и меняйте ее прямо поверх frontend без iframe.')
            : 'Кликните по элементу на странице и меняйте стиль сразу мышкой.';

        if (config.is_overlay && config.design_url && config.layout_url) {
            modeSwitch = doc.createElement('div');
            modeSwitch.className = 'ns-live-editor__mode-switch';

            var designLink = doc.createElement('a');
            var layoutLink = doc.createElement('a');

            designLink.className = 'ns-live-editor__mode-link' + (isDesignMode() ? ' is-active' : '');
            designLink.href = config.design_url;
            designLink.setAttribute('data-live-editor-mode-link', 'design');
            designLink.textContent = 'Design';

            layoutLink.className = 'ns-live-editor__mode-link' + (isLayoutMode() ? ' is-active' : '');
            layoutLink.href = config.layout_url;
            layoutLink.setAttribute('data-live-editor-mode-link', 'layout');
            layoutLink.textContent = 'Layout';

            modeSwitch.appendChild(designLink);
            modeSwitch.appendChild(layoutLink);
        }

        close.className = 'ns-live-editor__icon';
        close.type = 'button';
        close.textContent = '×';
        close.addEventListener('click', function () {
            shell.classList.toggle('is-collapsed');
        });

        titleWrap.appendChild(title);
        titleWrap.appendChild(subtitle);
        if (modeSwitch) {
            titleWrap.appendChild(modeSwitch);
        }
        header.appendChild(titleWrap);
        header.appendChild(close);

        meta.className = 'ns-live-editor__meta';
        nameNode.className = 'ns-live-editor__picked';
        nameNode.setAttribute('data-live-editor-title', '1');
        nameNode.textContent = 'Кликните по элементу на странице';
        selectorNode.className = 'ns-live-editor__selector';
        selectorNode.setAttribute('data-live-editor-selector', '1');
        selectorNode.textContent = 'Пока не выбран';
        meta.appendChild(nameNode);
        meta.appendChild(selectorNode);

        switchers.className = 'ns-live-editor__switchers';
        deviceSelect.className = 'ns-live-editor__select';
        stateSelect.className = 'ns-live-editor__select';
        [
            { value: 'base', label: 'Desktop' },
            { value: 'tablet', label: 'Tablet' },
            { value: 'mobile', label: 'Mobile' }
        ].forEach(function (item) {
            var option = doc.createElement('option');
            option.value = item.value;
            option.textContent = item.label;
            deviceSelect.appendChild(option);
        });
        [
            { value: 'default', label: 'Обычный' },
            { value: 'hover', label: 'Наведение' }
        ].forEach(function (item) {
            var option = doc.createElement('option');
            option.value = item.value;
            option.textContent = item.label;
            stateSelect.appendChild(option);
        });
        deviceSelect.addEventListener('change', function () {
            deviceKey = deviceSelect.value || 'base';
            syncForm();
        });
        stateSelect.addEventListener('change', function () {
            stateKey = stateSelect.value || 'default';
            syncForm();
        });
        switchers.appendChild(deviceSelect);
        switchers.appendChild(stateSelect);

        note.className = 'ns-live-editor__note';
        note.setAttribute('data-live-editor-device-note', '1');

        if (config.is_overlay && isLayoutMode()) {
            sectionsBlock = doc.createElement('div');
            sectionsBlock.className = 'ns-live-editor__section';

            var sectionsTitle = doc.createElement('div');
            sectionsTitle.className = 'ns-live-editor__section-title';
            sectionsTitle.textContent = 'Секции';

            var sectionsHint = doc.createElement('div');
            sectionsHint.className = 'ns-live-editor__section-hint';
            sectionsHint.textContent = 'Кликните по месту на странице и добавьте новый блок сразу в draft-layout.';

            insertContext = doc.createElement('div');
            insertContext.className = 'ns-live-editor__insert-context';
            insertContext.textContent = 'Точка вставки: в конец страницы.';

            presetList = doc.createElement('div');
            presetList.className = 'ns-live-editor__preset-list';

            getOverlayPresets().forEach(function (preset) {
                var button = doc.createElement('button');
                var presetTitle = doc.createElement('span');
                var presetDescription = doc.createElement('span');

                button.className = 'ns-live-editor__preset-card';
                button.type = 'button';
                button.disabled = !saveStateUrl;
                button.addEventListener('click', function () {
                    insertPreset(preset.key, preset.title);
                });

                presetTitle.className = 'ns-live-editor__preset-title';
                presetTitle.textContent = preset.title;
                presetDescription.className = 'ns-live-editor__preset-description';
                presetDescription.textContent = preset.description;

                button.appendChild(presetTitle);
                button.appendChild(presetDescription);
                presetList.appendChild(button);
            });

            sectionsBlock.appendChild(sectionsTitle);
            sectionsBlock.appendChild(sectionsHint);
            sectionsBlock.appendChild(insertContext);
            sectionsBlock.appendChild(presetList);

            blocksBlock = doc.createElement('div');
            blocksBlock.className = 'ns-live-editor__section';

            var blocksTitle = doc.createElement('div');
            blocksTitle.className = 'ns-live-editor__section-title';
            blocksTitle.textContent = 'Блоки';

            var blocksHint = doc.createElement('div');
            blocksHint.className = 'ns-live-editor__section-hint';
            blocksHint.textContent = 'Выберите колонку, секцию или существующий блок и добавьте system/custom блок в этот контекст.';

            var customCaption = doc.createElement('div');
            customCaption.className = 'ns-live-editor__section-caption';
            customCaption.textContent = 'Custom';

            var customList = doc.createElement('div');
            customList.className = 'ns-live-editor__preset-list';

            getCustomBlockPresets().forEach(function (preset) {
                var button = doc.createElement('button');
                var blockTitle = doc.createElement('span');
                var blockDescription = doc.createElement('span');

                button.className = 'ns-live-editor__preset-card';
                button.type = 'button';
                button.disabled = !saveStateUrl;
                button.addEventListener('click', function () {
                    insertBlock(createCustomBlock(preset.key, preset.title));
                });

                blockTitle.className = 'ns-live-editor__preset-title';
                blockTitle.textContent = preset.title;
                blockDescription.className = 'ns-live-editor__preset-description';
                blockDescription.textContent = preset.description;

                button.appendChild(blockTitle);
                button.appendChild(blockDescription);
                customList.appendChild(button);
            });

            var systemCaption = doc.createElement('div');
            systemCaption.className = 'ns-live-editor__section-caption';
            systemCaption.textContent = 'System';

            var systemList = doc.createElement('div');
            systemList.className = 'ns-live-editor__preset-list';

            getRecommendedSystemBlocks().forEach(function (libraryItem) {
                var button = doc.createElement('button');
                var blockTitle = doc.createElement('span');
                var blockDescription = doc.createElement('span');
                var descriptionText = libraryItem.description || (libraryItem.category_title + ' · ' + ((libraryItem.widget_controller || '') ? (libraryItem.widget_controller + '/' + libraryItem.widget_name) : libraryItem.widget_name));

                button.className = 'ns-live-editor__preset-card';
                button.type = 'button';
                button.disabled = !saveStateUrl;
                button.addEventListener('click', function () {
                    insertBlock(createSystemBlock(libraryItem));
                });

                blockTitle.className = 'ns-live-editor__preset-title';
                blockTitle.textContent = libraryItem.title;
                blockDescription.className = 'ns-live-editor__preset-description';
                blockDescription.textContent = descriptionText;

                button.appendChild(blockTitle);
                button.appendChild(blockDescription);
                systemList.appendChild(button);
            });

            blocksBlock.appendChild(blocksTitle);
            blocksBlock.appendChild(blocksHint);
            blocksBlock.appendChild(customCaption);
            blocksBlock.appendChild(customList);
            blocksBlock.appendChild(systemCaption);
            blocksBlock.appendChild(systemList);

            contentBlock = doc.createElement('div');
            contentBlock.className = 'ns-live-editor__section';

            var contentTitle = doc.createElement('div');
            contentTitle.className = 'ns-live-editor__section-title';
            contentTitle.textContent = 'Контент секции';

            contentHint = doc.createElement('div');
            contentHint.className = 'ns-live-editor__section-hint';
            contentHint.textContent = 'Выберите вставленную draft-секцию, чтобы быстро поправить ее текст.';

            contentForm = doc.createElement('div');
            contentForm.className = 'ns-live-editor__content-form';
            contentForm.hidden = true;
            contentForm.appendChild(buildOverlayContentControl('Название секции', 'section_title', 'input', 'Например: Hero'));
            contentForm.appendChild(buildOverlayContentControl('Eyebrow', 'eyebrow', 'input', 'Короткий kicker'));
            contentForm.appendChild(buildOverlayContentControl('Заголовок', 'heading', 'input', 'Заголовок секции'));
            contentForm.appendChild(buildOverlayContentControl('Текст', 'text', 'textarea', 'Основной текст'));
            contentForm.appendChild(buildOverlayContentControl('Список', 'items', 'textarea', 'Каждый пункт с новой строки'));
            contentForm.appendChild(buildOverlayContentControl('Текст кнопки', 'button_text', 'input', 'Оставить заявку'));
            contentForm.appendChild(buildOverlayContentControl('Ссылка кнопки', 'button_url', 'input', '#contact'));
            contentForm.appendChild(buildOverlayContentControl('HTML', 'html', 'textarea', '<div>Ваш HTML</div>'));

            contentSaveButton = doc.createElement('button');
            contentSaveButton.className = 'ns-live-editor__button ns-live-editor__button--primary';
            contentSaveButton.type = 'button';
            contentSaveButton.textContent = 'Сохранить контент секции';
            contentSaveButton.addEventListener('click', saveSelectedPresetContent);
            contentForm.appendChild(contentSaveButton);

            contentBlock.appendChild(contentTitle);
            contentBlock.appendChild(contentHint);
            contentBlock.appendChild(contentForm);

            instantMoveBlock = doc.createElement('div');
            instantMoveBlock.className = 'ns-live-editor__section';

            var instantMoveTitle = doc.createElement('div');
            instantMoveTitle.className = 'ns-live-editor__section-title';
            instantMoveTitle.textContent = 'Перемещение блока';

            var instantMoveHint = doc.createElement('div');
            instantMoveHint.className = 'ns-live-editor__section-hint';
            instantMoveHint.textContent = 'Выберите реальный Instant block, чтобы переместить его между positions ядра.';

            var instantMovePanel = doc.createElement('div');
            instantMovePanel.className = 'ns-live-editor__widget-options';
            instantMovePanel.hidden = true;

            var instantMoveSelect = doc.createElement('select');
            instantMoveSelect.className = 'ns-live-editor__select';

            var instantMoveButton = doc.createElement('button');
            instantMoveButton.className = 'ns-live-editor__button ns-live-editor__button--primary';
            instantMoveButton.type = 'button';
            instantMoveButton.textContent = 'Переместить блок';
            instantMoveButton.addEventListener('click', saveSelectedInstantMove);

            instantMovePanel.appendChild(instantMoveSelect);
            instantMovePanel.appendChild(instantMoveButton);

            instantMoveBlock.appendChild(instantMoveTitle);
            instantMoveBlock.appendChild(instantMoveHint);
            instantMoveBlock.appendChild(instantMovePanel);

            widgetOptionsBlock = doc.createElement('div');
            widgetOptionsBlock.className = 'ns-live-editor__section';

            var widgetOptionsTitle = doc.createElement('div');
            widgetOptionsTitle.className = 'ns-live-editor__section-title';
            widgetOptionsTitle.textContent = 'Настройки блока';

            var widgetOptionsHint = doc.createElement('div');
            widgetOptionsHint.className = 'ns-live-editor__section-hint';
            widgetOptionsHint.textContent = 'Выберите system block, чтобы открыть его реальные настройки InstantCMS прямо в layout mode.';

            var widgetOptionsPanel = doc.createElement('div');
            widgetOptionsPanel.className = 'ns-live-editor__widget-options';
            widgetOptionsPanel.hidden = true;

            var widgetOptionsFormHost = doc.createElement('div');
            widgetOptionsFormHost.className = 'ns-live-editor__widget-options-form';

            var widgetOptionsSaveButton = doc.createElement('button');
            widgetOptionsSaveButton.className = 'ns-live-editor__button ns-live-editor__button--primary';
            widgetOptionsSaveButton.type = 'button';
            widgetOptionsSaveButton.textContent = 'Сохранить настройки блока';
            widgetOptionsSaveButton.addEventListener('click', saveSelectedWidgetOptions);

            widgetOptionsPanel.appendChild(widgetOptionsFormHost);
            widgetOptionsPanel.appendChild(widgetOptionsSaveButton);

            widgetOptionsBlock.appendChild(widgetOptionsTitle);
            widgetOptionsBlock.appendChild(widgetOptionsHint);
            widgetOptionsBlock.appendChild(widgetOptionsPanel);

            overlayNodes.widgetOptionsHint = widgetOptionsHint;
            overlayNodes.widgetOptionsPanel = widgetOptionsPanel;
            overlayNodes.widgetOptionsFormHost = widgetOptionsFormHost;
            overlayNodes.widgetOptionsSaveButton = widgetOptionsSaveButton;
            overlayNodes.instantMoveHint = instantMoveHint;
            overlayNodes.instantMovePanel = instantMovePanel;
            overlayNodes.instantMoveSelect = instantMoveSelect;
            overlayNodes.instantMoveButton = instantMoveButton;
        }

        fieldsGrid.className = 'ns-live-editor__grid';
        fieldsGrid.appendChild(buildField('Цвет текста', 'color', '#173042'));
        fieldsGrid.appendChild(buildField('Фон', 'background-color', '#ffffff'));
        fieldsGrid.appendChild(buildField('Размер шрифта', 'font-size', '18px'));
        fieldsGrid.appendChild(buildField('Отступ', 'padding', '24px'));
        fieldsGrid.appendChild(buildField('Радиус', 'border-radius', '16px'));
        fieldsGrid.appendChild(buildField('Толщина рамки', 'border-width', '1px'));
        fieldsGrid.appendChild(buildField('Цвет рамки', 'border-color', '#d5cec0'));
        fieldsGrid.appendChild(buildField('Стиль рамки', 'border-style', 'solid'));

        actions.className = 'ns-live-editor__actions';
        saveButton.className = 'ns-live-editor__button ns-live-editor__button--primary';
        saveButton.type = 'button';
        saveButton.textContent = 'Сохранить на сайт';
        saveButton.addEventListener('click', saveRule);
        exitButton.className = 'ns-live-editor__button ns-live-editor__button--ghost';
        exitButton.href = config.exit_url || '#';
        exitButton.textContent = 'Выйти';
        rulesButton.className = 'ns-live-editor__button ns-live-editor__button--ghost';
        rulesButton.href = config.rules_url || '#';
        rulesButton.target = '_blank';
        rulesButton.rel = 'noopener';
        rulesButton.textContent = 'Открыть правила';
        if (isLayoutMode() && config.design_url) {
            overlayNodes.handoffButton = doc.createElement('a');
            overlayNodes.handoffButton.className = 'ns-live-editor__button ns-live-editor__button--primary';
            overlayNodes.handoffButton.href = config.design_url;
            overlayNodes.handoffButton.textContent = 'В design';
            actions.appendChild(overlayNodes.handoffButton);
        }
        if (isDesignMode()) {
            actions.appendChild(saveButton);
        }
        if (config.is_overlay && config.exit_url) {
            actions.appendChild(exitButton);
        }
        if (isDesignMode()) {
            actions.appendChild(rulesButton);
        }

        status.className = 'ns-live-editor__status';
        status.setAttribute('data-live-editor-status', '1');
        status.textContent = isLayoutMode()
            ? 'Выберите место на странице для вставки новой секции.'
            : 'Выберите элемент на странице.';

        shell.appendChild(header);
        shell.appendChild(meta);
        shell.appendChild(note);
        if (isDesignMode()) {
            shell.appendChild(switchers);
        }
        if (sectionsBlock) {
            shell.appendChild(sectionsBlock);
        }
        if (blocksBlock) {
            shell.appendChild(blocksBlock);
        }
        if (contentBlock) {
            shell.appendChild(contentBlock);
        }
        if (instantMoveBlock) {
            shell.appendChild(instantMoveBlock);
        }
        if (widgetOptionsBlock) {
            shell.appendChild(widgetOptionsBlock);
        }
        if (isDesignMode()) {
            shell.appendChild(fieldsGrid);
        }
        if (actions.childNodes.length) {
            shell.appendChild(actions);
        }
        shell.appendChild(status);

        body.appendChild(shell);

        overlayNodes.insertContext = insertContext;
        overlayNodes.contentHint = contentHint;
        overlayNodes.contentForm = contentForm;
        overlayNodes.contentSaveButton = contentSaveButton;

        return shell;
    }

    panel = buildPanel();
    if (isEditorShell) {
        panel.style.display = 'none';
    }
    ensureOverlay();
    ensurePreviewStyleNode();
    renderLayoutState();
    loadInstantScheme();
    syncForm();
    updateInsertContext();
    updateContentInspector();
    updateInstantMoveInspector();
    updateWidgetOptionsInspector();

    (function focusRequestedNode() {
        var focusKey = getRequestedFocusKey();
        var targetNode;

        if (!focusKey) {
            return;
        }

        targetNode = doc.querySelector(buildNodeSelector(focusKey));
        if (!targetNode) {
            return;
        }

        window.requestAnimationFrame(function () {
            targetNode.scrollIntoView({ block: 'center', inline: 'nearest' });
            selectElement(targetNode);
            setStatus('Новая draft-секция готова к редактированию.', 'ok');
        });
    })();

    doc.addEventListener('mousemove', function (event) {
        var target = event.target;

        updateColumnResize(event);

        if (resizeState.active || instantDragState.active) {
            return;
        }

        if (!target || target.nodeType !== 1 || target === panel || panel.contains(target)) {
            return;
        }

        hoveredElement = getEditableNode(target);

        if (!selectedElement) {
            positionOutline(hoveredElement);
        }
    }, true);

    doc.addEventListener('scroll', function () {
        positionOutline(selectedElement || hoveredElement);
    }, true);

    window.addEventListener('resize', function () {
        positionOutline(selectedElement || hoveredElement);
    });

    doc.addEventListener('mousedown', function (event) {
        var resizeHandle = event.target && event.target.closest ? event.target.closest('.ns-live-runtime__resize-handle') : null;

        if (!resizeHandle) {
            return;
        }

        beginColumnResize(resizeHandle, event);
    }, true);

    doc.addEventListener('dragstart', function (event) {
        var widgetNode = event.target && event.target.closest ? event.target.closest('.ns-live-runtime__block[data-instant-binding-page-id]') : null;

        if (!widgetNode) {
            return;
        }

        beginInstantWidgetDrag(widgetNode, event);
    }, true);

    doc.addEventListener('dragover', function (event) {
        var columnNode;

        if (!instantDragState.active) {
            return;
        }

        columnNode = getInstantDropColumnNode(event.target);
        setInstantDropHover(columnNode);

        if (!columnNode) {
            return;
        }

        event.preventDefault();

        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'move';
        }
    }, true);

    doc.addEventListener('drop', function (event) {
        var columnNode;
        var targetPosition;

        if (!instantDragState.active) {
            return;
        }

        columnNode = getInstantDropColumnNode(event.target);
        if (!columnNode) {
            return;
        }

        targetPosition = String(columnNode.getAttribute('data-instant-position') || '').trim();
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        instantDragState.ignoreClickUntil = Date.now() + 400;
        resetInstantDragState();
        updateInstantMoveInspector();
        triggerSelectedInstantMove(targetPosition, 'Перемещаю блок через drag and drop...');
    }, true);

    doc.addEventListener('dragend', function (event) {
        var widgetNode = event.target && event.target.closest ? event.target.closest('.ns-live-runtime__block[data-instant-binding-page-id]') : null;

        if (!instantDragState.active && !widgetNode) {
            return;
        }

        instantDragState.ignoreClickUntil = Date.now() + 400;
        resetInstantDragState();
        updateInstantMoveInspector();
    }, true);

    doc.addEventListener('mouseup', function () {
        finishColumnResize();
    }, true);

    doc.addEventListener('click', function (event) {
        var target = event.target;
        var resizeHandle = target && target.closest ? target.closest('.ns-live-runtime__resize-handle') : null;

        if (!target || target.nodeType !== 1) {
            return;
        }

        if (resizeHandle || resizeState.active || resizeState.ignoreClickUntil > Date.now() || instantDragState.active || instantDragState.ignoreClickUntil > Date.now()) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            return;
        }

        if (target === panel || panel.contains(target)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        selectElement(target.closest('*') || target);
    }, true);

    doc.addEventListener('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
        return;
    }

    boot();
})();