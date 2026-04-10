(function () {
    var root = document.querySelector('[data-nordic-builder]');
    if (!root) {
        return;
    }

    var builderState = window.NORDIC_BUILDER_STATE || {};
    var page = builderState.page || {};
    var canvas = root.querySelector('[data-builder-canvas]');
    var stage = root.querySelector('[data-builder-stage]') || canvas;
    var outline = root.querySelector('[data-builder-outline]');
    var statusNode = root.querySelector('[data-builder-status]');
    var viewportDeviceNode = root.querySelector('[data-viewport-device]');
    var viewportSourceNode = root.querySelector('[data-viewport-source]');
    var viewportSubtitleNode = root.querySelector('[data-viewport-subtitle]');
    var inspector = root.querySelector('[data-inspector]');
    var inspectorEmpty = inspector ? inspector.querySelector('.nb-inspector__empty') : null;
    var inspectorMeta = inspector ? inspector.querySelector('.nb-inspector__meta') : null;
    var inspectorType = inspector ? inspector.querySelector('[data-inspector-type]') : null;
    var inspectorTitle = inspector ? inspector.querySelector('[data-inspector-title]') : null;
    var inspectorWidth = inspector ? inspector.querySelector('[data-inspector-width]') : null;
    var settingsEmpty = root.querySelector('[data-node-settings-empty]');
    var settingsPanel = root.querySelector('[data-node-settings]');
    var titleInput = root.querySelector('[data-setting-input="title"]');
    var rowModeField = root.querySelector('[data-setting-scope="row"]');
    var rowModeInput = root.querySelector('[data-setting-input="width_mode"]');
    var columnWidthField = root.querySelector('[data-setting-scope="column"]');
    var columnWidthInput = root.querySelector('[data-setting-input="width"]');
    var columnWidthValue = root.querySelector('[data-setting-width-value]');
    var deviceOverrideNote = root.querySelector('[data-device-override-state]');
    var currentDeviceLabel = root.querySelector('[data-current-device-label]');
    var widgetFilterButtons = root.querySelectorAll('[data-widget-filter]');
    var widgetLibraryItems = root.querySelectorAll('[data-widget-library-item]');
    var pageSwitcher = root.querySelector('[data-page-switcher]');
    var deviceButtons = root.querySelectorAll('[data-device-button]');
    var saveStateUrl = String(builderState.state_url || '');
    var publishStateUrl = String(builderState.publish_url || '');
    var restoreStateUrl = String(builderState.restore_url || '');
    var styleRuleUrl = String(builderState.style_rule_url || '');
    var widgetOptionsUrl = String(builderState.widget_options_url || '');
    var resetStateUrl = String(builderState.reset_url || '');
    var pickerFrameUrl = String(builderState.picker_frame_url || '');
    var pickerOrigin = String(builderState.picker_origin || window.location.origin || '');
    var csrfToken = readCSRFToken();
    var publishButton = root.querySelector('[data-builder-publish]');
    var resetButton = root.querySelector('[data-builder-reset]');
    var columnInsertPanel = root.querySelector('[data-column-insert-panel]');
    var insertSelectedWidgetButton = root.querySelector('[data-insert-selected-widget]');
    var insertSectionButton = root.querySelector('[data-insert-section]');
    var selectedLibraryTitle = root.querySelector('[data-selected-library-title]');
    var selectedLibraryHint = root.querySelector('[data-selected-library-hint]');
    var widgetOptionsBlock = root.querySelector('[data-widget-options-block]');
    var widgetOptionsEmpty = root.querySelector('[data-widget-options-empty]');
    var widgetOptionsLock = root.querySelector('[data-widget-options-lock]');
    var widgetOptionsBody = root.querySelector('[data-widget-options-body]');
    var styleSection = root.querySelector('[data-style-inspector-block]');
    var styleSelectorInput = root.querySelector('[data-style-selector]');
    var styleTargetSource = root.querySelector('[data-style-target-source]');
    var styleTargetHint = root.querySelector('[data-style-target-hint]');
    var styleScopeNote = root.querySelector('[data-style-scope-note]');
    var styleFields = Array.prototype.slice.call(root.querySelectorAll('[data-style-field]'));
    var styleSaveButton = root.querySelector('[data-style-save]');
    var styleResetButton = root.querySelector('[data-style-reset]');
    var styleOpenPickerButton = root.querySelector('[data-style-open-picker]');
    var stylePickerModal = document.querySelector('[data-style-picker-modal]');
    var stylePickerFrame = document.querySelector('[data-style-picker-frame]');
    var stylePickerCloseButtons = Array.prototype.slice.call(document.querySelectorAll('[data-style-picker-close]'));
    var deviceLabels = {
        desktop: 'Desktop',
        tablet: 'Tablet',
        mobile: 'Mobile'
    };
    var styleDevices = ['base', 'mobile', 'tablet', 'desktop'];
    var styleStates = ['default', 'hover', 'active', 'focus', 'focus-visible', 'visited', 'before', 'after'];
    var styleSourceLabels = {
        manual: 'manual',
        picker: 'picker',
        builder: 'builder',
        selector_map: 'selector map'
    };
    var selectedNodeUid = null;
    var selectedLibraryCard = null;
    var dragState = null;
    var currentWidgetFilter = 'all';
    var uidCounter = 1;
    var saveTimer = null;
    var loadedWidgetOptionsUid = '';
    var widgetOptionsRequestToken = 0;
    var currentStyleRule = createEmptyStyleRule('');
    var styleRuleRequestToken = 0;
    var loadedStyleKey = '';
    var storageKey = buildStorageKey(page);
    var layoutState = normalizeLayoutState(builderState.layout_state || loadStoredLayoutState() || {}, builderState.rows || []);
    var activeDevice = normalizeDeviceKey(page.device || 'desktop');
    var currentRows = resolveRowsForDevice(activeDevice);

    function readCSRFToken() {
        var tokenNode = document.querySelector('meta[name="csrf-token"]');

        return tokenNode ? String(tokenNode.getAttribute('content') || '') : '';
    }

    function buildStorageKey(pageState) {
        return ['nordic-builder-layout', pageState.template || 'template', pageState.uri || '/'].join(':');
    }

    function normalizeDeviceKey(deviceKey) {
        return deviceLabels[deviceKey] ? deviceKey : 'desktop';
    }

    function loadStoredLayoutState() {
        if (!window.localStorage) {
            return null;
        }

        try {
            var raw = window.localStorage.getItem(storageKey);
            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            return null;
        }
    }

    function saveStoredLayoutState() {
        if (!window.localStorage) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, JSON.stringify(layoutState));
        } catch (error) {
            setStatus('Локальное сохранение overrides недоступно.');
        }
    }

    function serializeRequestBody(payload) {
        return Object.keys(payload).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(String(payload[key]));
        }).join('&');
    }

    function deepClone(value) {
        if (typeof value === 'undefined') {
            return undefined;
        }

        return JSON.parse(JSON.stringify(value));
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function makeUid(prefix) {
        uidCounter += 1;
        return prefix + '-' + Date.now().toString(36) + '-' + uidCounter;
    }

    function clampUnits(value) {
        if (value < 2) {
            return 2;
        }
        if (value > 12) {
            return 12;
        }
        return value;
    }

    function defaultTitle(type) {
        var titles = {
            row: 'Ряд',
            column: 'Колонка',
            widget: 'Виджет'
        };

        return titles[type] || 'Узел';
    }

    function duplicateTitle(title) {
        return (title || 'Узел') + ' копия';
    }

    function normalizeLayoutState(state, fallbackRows) {
        return {
            desktop: {
                rows: normalizeRows(state && state.desktop && Array.isArray(state.desktop.rows) ? state.desktop.rows : fallbackRows),
                overrides: {}
            },
            tablet: {
                rows: null,
                overrides: normalizeOverrides(state && state.tablet ? state.tablet.overrides : null)
            },
            mobile: {
                rows: null,
                overrides: normalizeOverrides(state && state.mobile ? state.mobile.overrides : null)
            }
        };
    }

    function normalizeOverrides(overrides) {
        var normalized = {};

        if (overrides && Array.isArray(overrides.rows)) {
            normalized.rows = normalizeRows(overrides.rows);
        }

        return normalized;
    }

    function normalizeRows(rows) {
        if (!Array.isArray(rows)) {
            return [];
        }

        return rows.map(function (row) {
            return normalizeRow(row);
        });
    }

    function normalizeRow(row) {
        row = row || {};

        return {
            uid: row.uid || makeUid('row'),
            title: String(row.title || defaultTitle('row')),
            kind: 'row',
            width_mode: row.width_mode === 'full' ? 'full' : 'grid',
            hidden: Boolean(row.hidden),
            meta: row.meta && typeof row.meta === 'object' ? deepClone(row.meta) : {},
            columns: Array.isArray(row.columns) ? row.columns.map(function (column) {
                return normalizeColumn(column);
            }) : []
        };
    }

    function normalizeColumn(column) {
        column = column || {};

        return {
            uid: column.uid || makeUid('column'),
            title: String(column.title || defaultTitle('column')),
            kind: 'column',
            width: clampUnits(parseInt(column.width || 12, 10) || 12),
            hidden: Boolean(column.hidden),
            meta: column.meta && typeof column.meta === 'object' ? deepClone(column.meta) : {},
            widgets: dedupeWidgets(Array.isArray(column.widgets) ? column.widgets.map(function (widget) {
                return normalizeWidget(widget);
            }) : []),
            nested_rows: Array.isArray(column.nested_rows) ? column.nested_rows.map(function (nestedRow) {
                return normalizeRow(nestedRow);
            }) : []
        };
    }

    function dedupeWidgets(widgets) {
        var seen = {};

        return (widgets || []).filter(function (widget) {
            var bindId = parseInt(widget.bind_id || 0, 10) || 0;
            var positionName = String(widget.position_name || '');
            var sourcePageId = parseInt(widget.source_page_id || 0, 10) || 0;
            var key;
            var existing;

            if (bindId < 1 || !positionName) {
                return true;
            }

            key = String(bindId) + ':' + positionName;
            existing = seen[key];

            if (!existing) {
                seen[key] = widget;
                return true;
            }

            if ((parseInt(existing.source_page_id || 0, 10) || 0) === 1 && sourcePageId === 0) {
                seen[key] = widget;
                return true;
            }

            return false;
        });
    }

    function normalizeWidget(widget) {
        widget = widget || {};

        return {
            uid: widget.uid || makeUid('widget'),
            title: String(widget.title || defaultTitle('widget')),
            kind: 'widget',
            source: String(widget.source || 'system'),
            widget_id: parseInt(widget.widget_id || 0, 10) || 0,
            widget_name: String(widget.widget_name || ''),
            widget_controller: String(widget.widget_controller || ''),
            bind_id: parseInt(widget.bind_id || 0, 10) || 0,
            binding_page_id: parseInt(widget.binding_page_id || 0, 10) || 0,
            source_page_id: parseInt(widget.source_page_id || 0, 10) || 0,
            position_name: String(widget.position_name || ''),
            library_uid: String(widget.library_uid || ''),
            has_options: Boolean(widget.has_options || (parseInt(widget.widget_id || 0, 10) || 0) > 0),
            can_edit_options: typeof widget.can_edit_options === 'boolean' ? widget.can_edit_options : ((parseInt(widget.bind_id || 0, 10) || 0) < 1 || (parseInt(widget.source_page_id || 0, 10) || 0) === 1),
            bind_config: normalizeWidgetBindConfig(widget.bind_config, widget),
            hidden: Boolean(widget.hidden)
        };
    }

    function createDefaultColumn(title) {
        return normalizeColumn({
            title: title || 'Колонка',
            width: 12,
            widgets: [],
            nested_rows: []
        });
    }

    function createDefaultRow(title) {
        return normalizeRow({
            title: title || 'Новый ряд',
            width_mode: 'grid',
            columns: [createDefaultColumn('Колонка 1')]
        });
    }

    function buildWidgetFromLibraryCard(card) {
        return normalizeWidget({
            title: String(card.getAttribute('data-widget-title') || 'Виджет'),
            source: String(card.getAttribute('data-widget-controller') || 'system') || 'system',
            widget_id: parseInt(card.getAttribute('data-widget-id') || '0', 10) || 0,
            widget_name: String(card.getAttribute('data-widget-name') || ''),
            widget_controller: String(card.getAttribute('data-widget-controller') || ''),
            has_options: String(card.getAttribute('data-widget-has-options') || '') === '1',
            library_uid: String(card.getAttribute('data-widget-category') || '') + ':' + String(card.getAttribute('data-widget-name') || '')
        });
    }

    function buildDefaultWidgetBindConfig(widget) {
        return {
            title: String(widget && widget.title ? widget.title : defaultTitle('widget')),
            template: String(page.template || ''),
            is_title: true,
            is_tab_prev: false,
            is_cacheable: false,
            links: '',
            tpl_wrap: 'wrapper',
            tpl_wrap_style: '',
            tpl_wrap_custom: '',
            tpl_body: String(widget && widget.widget_name ? widget.widget_name : ''),
            class_wrap: '',
            class_title: '',
            class: '',
            groups_view: [],
            groups_hide: [],
            languages: [],
            device_types: [],
            template_layouts: [],
            url_mask_not: '',
            options: {}
        };
    }

    function normalizeWidgetBindConfig(config, widget) {
        var normalized = buildDefaultWidgetBindConfig(widget || {});

        if (config && typeof config === 'object' && !Array.isArray(config)) {
            Object.keys(normalized).forEach(function (key) {
                if (typeof config[key] !== 'undefined') {
                    normalized[key] = deepClone(config[key]);
                }
            });
        }

        ['groups_view', 'groups_hide', 'languages', 'device_types', 'template_layouts'].forEach(function (key) {
            if (!Array.isArray(normalized[key])) {
                normalized[key] = [];
            }
        });

        if (!normalized.options || typeof normalized.options !== 'object' || Array.isArray(normalized.options)) {
            normalized.options = {};
        }

        normalized.title = String(normalized.title || (widget && widget.title) || defaultTitle('widget')).trim() || defaultTitle('widget');
        normalized.template = String(normalized.template || page.template || '');
        normalized.tpl_body = String(normalized.tpl_body || (widget && widget.widget_name) || '');
        normalized.links = String(normalized.links || '');
        normalized.tpl_wrap = String(normalized.tpl_wrap || 'wrapper');
        normalized.tpl_wrap_style = String(normalized.tpl_wrap_style || '');
        normalized.tpl_wrap_custom = String(normalized.tpl_wrap_custom || '');
        normalized.class_wrap = String(normalized.class_wrap || '');
        normalized.class_title = String(normalized.class_title || '');
        normalized.class = String(normalized.class || '');
        normalized.url_mask_not = String(normalized.url_mask_not || '');

        return normalized;
    }

    function getParentDevice(deviceKey) {
        if (deviceKey === 'mobile') {
            return 'tablet';
        }
        if (deviceKey === 'tablet') {
            return 'desktop';
        }

        return null;
    }

    function hasOverride(deviceKey) {
        if (deviceKey === 'desktop') {
            return true;
        }

        return Boolean(layoutState[deviceKey] && Array.isArray(layoutState[deviceKey].overrides.rows));
    }

    function getDevicePreviewSource(deviceKey) {
        if (deviceKey === 'desktop') {
            return 'base';
        }

        return hasOverride(deviceKey) ? 'override' : 'inherited';
    }

    function resolveRowsForDevice(deviceKey) {
        deviceKey = normalizeDeviceKey(deviceKey);

        if (deviceKey === 'desktop') {
            return normalizeRows(deepClone(layoutState.desktop.rows || []));
        }

        if (hasOverride(deviceKey)) {
            return normalizeRows(deepClone(layoutState[deviceKey].overrides.rows || []));
        }

        return resolveRowsForDevice(getParentDevice(deviceKey));
    }

    function persistCurrentRows() {
        if (activeDevice === 'desktop') {
            layoutState.desktop.rows = deepClone(currentRows);
        } else {
            layoutState[activeDevice].overrides.rows = deepClone(currentRows);
        }

        saveStoredLayoutState();
        queueRemoteSave();
    }

    function queueRemoteSave() {
        if (!saveStateUrl || !window.fetch || !csrfToken) {
            return;
        }

        if (saveTimer) {
            window.clearTimeout(saveTimer);
        }

        saveTimer = window.setTimeout(function () {
            flushRemoteSave();
        }, 220);
    }

    function flushRemoteSave() {
        var body;

        if (!saveStateUrl || !window.fetch || !csrfToken) {
            return;
        }

        saveTimer = null;
        body = serializeRequestBody({
            csrf_token: csrfToken,
            template: page.template || '',
            uri: page.uri || '/',
            layout_state: JSON.stringify(layoutState)
        });

        window.fetch(saveStateUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                setStatus((response && response.message) ? response.message : 'Не удалось сохранить builder state на сервере.');
            }
        }).catch(function () {
            setStatus('Серверное сохранение builder state сейчас недоступно.');
        });
    }

    function publishDesktopLayout() {
        var body;

        if (!publishStateUrl || !window.fetch || !csrfToken) {
            setStatus('Сохранение на сайт сейчас недоступно.');
            return;
        }

        if (publishButton) {
            publishButton.disabled = true;
        }

        body = serializeRequestBody({
            csrf_token: csrfToken,
            template: page.template || '',
            source_template: page.default_source || '',
            uri: page.uri || '/',
            layout_state: JSON.stringify(layoutState)
        });

        window.fetch(publishStateUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                setStatus((response && response.message) ? response.message : 'Не удалось сохранить изменения на сайт.');
                return;
            }

            setStatus((response.message || 'Изменения сохранены на сайт.') + ' Рядов: ' + String(response.rows || 0) + ', колонок: ' + String(response.columns || 0) + ', виджетов: ' + String(response.widgets || 0) + '.');
        }).catch(function () {
            setStatus('Сохранение на сайт сейчас недоступно.');
        }).finally(function () {
            if (publishButton) {
                publishButton.disabled = false;
            }
        });
    }

    function resetTemplateToDefault() {
        var body;

        if (!resetStateUrl || !window.fetch || !csrfToken || !page.default_source) {
            setStatus('Reset-to-default сейчас недоступен.');
            return;
        }

        if (!window.confirm('Вернуть шаблон ' + String(page.template || '') + ' к default-схеме ' + String(page.default_source || '') + '? Это заменит native layout и bindings.')) {
            return;
        }

        if (resetButton) {
            resetButton.disabled = true;
        }

        body = serializeRequestBody({
            csrf_token: csrfToken,
            template: page.template || '',
            source_template: page.default_source || '',
            uri: page.uri || '/'
        });

        window.fetch(resetStateUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                setStatus((response && response.message) ? response.message : 'Не удалось вернуть default-схему.');
                return;
            }

            setStatus((response.message || 'Default-схема восстановлена.') + ' Рядов: ' + String(response.rows || 0) + ', колонок: ' + String(response.columns || 0) + ', виджетов: ' + String(response.widgets || 0) + '.');
            window.setTimeout(function () {
                window.location.reload();
            }, 500);
        }).catch(function () {
            setStatus('Reset-to-default сейчас недоступен.');
        }).finally(function () {
            if (resetButton) {
                resetButton.disabled = false;
            }
        });
    }

    function restoreRevision(revisionId) {
        var body;

        if (!restoreStateUrl || !window.fetch || !csrfToken || revisionId < 1) {
            setStatus('Восстановление ревизии сейчас недоступно.');
            return;
        }

        if (!window.confirm('Восстановить ревизию #' + String(revisionId) + ' и сразу применить ее на сайт?')) {
            return;
        }

        body = serializeRequestBody({
            csrf_token: csrfToken,
            template: page.template || '',
            source_template: page.default_source || '',
            uri: page.uri || '/',
            revision_id: revisionId
        });

        window.fetch(restoreStateUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                setStatus((response && response.message) ? response.message : 'Не удалось восстановить ревизию.');
                return;
            }

            setStatus(response.message || 'Ревизия восстановлена.');
            window.setTimeout(function () {
                window.location.reload();
            }, 350);
        }).catch(function () {
            setStatus('Восстановление ревизии сейчас недоступно.');
        });
    }

    function renderNodeActions(node) {
        var hideTitle = node.hidden ? 'Показать узел' : 'Скрыть узел';
        var hideIcon = node.hidden ? '◌' : '◐';

        return [
            '<div class="nb-node-actions">',
            '<button class="nb-node-action" type="button" data-node-action="settings" title="Настройки">⚙</button>',
            '<button class="nb-node-action" type="button" data-node-action="duplicate" title="Дублировать">⧉</button>',
            '<button class="nb-node-action" type="button" data-node-action="hide" title="' + escapeHtml(hideTitle) + '">' + hideIcon + '</button>',
            '<button class="nb-node-action nb-node-action--danger" type="button" data-node-action="delete" title="Удалить">✕</button>',
            '</div>'
        ].join('');
    }

    function renderDeviceBadge(node) {
        var label = 'Base';
        var className = 'nb-node-badge nb-node-badge--base';

        if (activeDevice !== 'desktop') {
            if (node.hidden) {
                label = 'Hidden';
                className = 'nb-node-badge nb-node-badge--hidden';
            } else if (hasOverride(activeDevice)) {
                label = 'Override';
                className = 'nb-node-badge nb-node-badge--override';
            } else {
                label = 'Inherited';
                className = 'nb-node-badge nb-node-badge--inherited';
            }
        }

        return '<span class="' + className + '">' + escapeHtml(label) + '</span>';
    }

    function renderRows(rows, isNested) {
        return rows.map(function (row) {
            return renderRow(row, isNested);
        }).join('');
    }

    function renderRow(row, isNested) {
        var classes = ['nb-row'];
        var columnsMarkup = [];
        var columns = Array.isArray(row.columns) ? row.columns : [];

        if (isNested) {
            classes.push('nb-row--nested');
        }
        if (row.width_mode === 'full') {
            classes.push('nb-row--full');
        }
        if (row.hidden) {
            classes.push('is-hidden');
        }

        columns.forEach(function (column, index) {
            columnsMarkup.push(renderColumn(column));
            if (index < columns.length - 1) {
                columnsMarkup.push(
                    '<div class="nb-resize-handle" data-resize-handle data-left-uid="' + escapeHtml(column.uid) + '" data-right-uid="' + escapeHtml(columns[index + 1].uid) + '" aria-label="Изменить ширину колонок" title="Потяни, чтобы изменить ширину"></div>'
                );
            }
        });

        return [
            '<section class="' + classes.join(' ') + '" data-builder-node data-node-type="row" data-node-uid="' + escapeHtml(row.uid) + '" data-node-title="' + escapeHtml(row.title) + '">',
            '<header class="nb-row__header">',
            '<div class="nb-row__heading">',
            '<div class="nb-row__label" data-node-text="title">' + escapeHtml(row.title) + '</div>' + renderDeviceBadge(row),
            '<div class="nb-row__mode" data-row-mode>' + escapeHtml(row.width_mode) + '</div>',
            '</div>',
            '<div class="nb-row__tools">',
            renderNodeActions(row),
            '<button class="nb-add-button" type="button" data-builder-add data-add-kind="row" title="Добавить ряд или секцию">+</button>',
            '</div>',
            '</header>',
            '<div class="nb-row__columns" data-columns-row>',
            columnsMarkup.join(''),
            '</div>',
            '</section>'
        ].join('');
    }

    function renderColumn(column) {
        var classes = ['nb-column'];
        var widgetsMarkup = [];

        if (column.hidden) {
            classes.push('is-hidden');
        }

        (column.widgets || []).forEach(function (widget) {
            widgetsMarkup.push(renderWidget(widget));
        });

        if (Array.isArray(column.nested_rows) && column.nested_rows.length) {
            widgetsMarkup.push(
                '<div class="nb-column__nested">' +
                '<div class="nb-column__nested-label">Секции</div>' +
                renderRows(column.nested_rows, true) +
                '</div>'
            );
        }

        return [
            '<div class="' + classes.join(' ') + '" data-builder-node data-node-type="column" data-node-uid="' + escapeHtml(column.uid) + '" data-node-title="' + escapeHtml(column.title) + '" data-column data-units="' + column.width + '" style="--nb-col-span: ' + column.width + ';">',
            '<div class="nb-column__chrome">',
            '<div class="nb-column__title-group">',
            '<div class="nb-column__title" data-node-text="title">' + escapeHtml(column.title) + '</div>' + renderDeviceBadge(column),
            '<div class="nb-column__width"><span data-column-width-label>' + column.width + '/12</span></div>',
            '</div>',
            '<div class="nb-column__tools">' + renderNodeActions(column) + '</div>',
            '</div>',
            '<div class="nb-column__body">',
            widgetsMarkup.join(''),
            '<button class="nb-add-slot" type="button" data-builder-add data-add-kind="widget" title="Добавить виджет">+</button>',
            '</div>',
            '</div>'
        ].join('');
    }

    function renderWidget(widget) {
        var classes = ['nb-widget'];

        if (widget.hidden) {
            classes.push('is-hidden');
        }

        return [
            '<article class="' + classes.join(' ') + '" data-builder-node data-node-type="widget" data-node-uid="' + escapeHtml(widget.uid) + '" data-node-title="' + escapeHtml(widget.title) + '">',
            '<div class="nb-widget__head">',
            '<div class="nb-widget__text">',
            '<div class="nb-widget__source">' + escapeHtml(widget.source) + '</div>',
            '<div class="nb-widget__title" data-node-text="title">' + escapeHtml(widget.title) + '</div>' + renderDeviceBadge(widget),
            '</div>',
            renderNodeActions(widget),
            '</div>',
            '</article>'
        ].join('');
    }

    function renderCanvas() {
        if (!stage) {
            return;
        }

        if (!currentRows.length) {
            stage.innerHTML = '<section class="nb-builder__panel nb-canvas-empty"><h2>Схема пока не найдена</h2><div class="nb-builder__hint">Для текущего режима нет узлов. Первый override создастся после изменения структуры.</div><button class="nb-add-button" type="button" data-builder-add data-add-kind="row" title="Добавить первый ряд">+</button></section>';
            return;
        }

        stage.innerHTML = renderRows(currentRows, false);
    }

    function renderOutline() {
        if (!outline) {
            return;
        }

        if (!currentRows.length) {
            outline.innerHTML = '<li><span class="nb-builder__hint">Пока пусто</span></li>';
            return;
        }

        outline.innerHTML = currentRows.map(function (row) {
            var classes = ['nb-outline__item'];
            if (row.hidden) {
                classes.push('is-hidden');
            }

            return '<li><button class="' + classes.join(' ') + '" type="button" data-select-target="' + escapeHtml(row.uid) + '">' + escapeHtml(row.title) + '</button></li>';
        }).join('');
    }

    function setStatus(text) {
        if (statusNode) {
            statusNode.textContent = text;
        }
    }

    function syncWidgetTitleIntoBindConfig(widget) {
        if (!widget || widget.kind !== 'widget') {
            return;
        }

        widget.bind_config = normalizeWidgetBindConfig(widget.bind_config, widget);
        widget.bind_config.title = String(widget.title || defaultTitle('widget')).trim() || defaultTitle('widget');
    }

    function updateSelectedLibrarySummary() {
        var libraryTitle = selectedLibraryCard ? String(selectedLibraryCard.getAttribute('data-widget-title') || 'Виджет') : 'Ничего не выбрано';
        var record = getSelectedRecord();

        if (selectedLibraryTitle) {
            selectedLibraryTitle.textContent = libraryTitle;
        }

        if (selectedLibraryHint) {
            selectedLibraryHint.textContent = selectedLibraryCard
                ? 'Этот виджет вставится в текущую колонку без перехода на другой экран.'
                : 'Выберите карточку в библиотеке слева, чтобы вставить ее в текущую колонку.';
        }

        if (insertSelectedWidgetButton) {
            insertSelectedWidgetButton.disabled = !selectedLibraryCard || !record || record.type !== 'column';
        }
        if (insertSectionButton) {
            insertSectionButton.disabled = !record || record.type !== 'column';
        }
    }

    function syncWidgetOptionsFormTitle(value) {
        var titleField = widgetOptionsBody ? widgetOptionsBody.querySelector('[name="title"]') : null;

        if (titleField && titleField.value !== value) {
            titleField.value = value;
        }
    }

    function showWidgetOptionsMessage(message, isLock) {
        if (!widgetOptionsBlock) {
            return;
        }

        if (widgetOptionsEmpty) {
            widgetOptionsEmpty.hidden = Boolean(isLock);
            widgetOptionsEmpty.textContent = message || 'Выберите виджет, чтобы открыть его настройки.';
        }

        if (widgetOptionsLock) {
            widgetOptionsLock.hidden = !isLock;
            if (isLock) {
                widgetOptionsLock.textContent = message || 'Этот виджет пришел из default-схемы. Чтобы менять его настройки безопасно, продублируйте виджет и настройте копию.';
            }
        }

        if (widgetOptionsBody) {
            widgetOptionsBody.hidden = true;
            widgetOptionsBody.innerHTML = '';
        }

        loadedWidgetOptionsUid = '';
    }

    function renderWidgetOptionsForm(html) {
        if (!widgetOptionsBody) {
            return;
        }

        widgetOptionsBody.innerHTML = '<form class="nb-widget-options-form" data-widget-options-form>' + html + '</form>';
        widgetOptionsBody.hidden = false;

        if (widgetOptionsEmpty) {
            widgetOptionsEmpty.hidden = true;
        }
        if (widgetOptionsLock) {
            widgetOptionsLock.hidden = true;
        }
    }

    function loadWidgetOptions(record) {
        var requestToken;
        var body;

        if (!widgetOptionsBlock || !record || record.type !== 'widget') {
            return;
        }

        if (!record.node.can_edit_options) {
            showWidgetOptionsMessage('Этот виджет пришел из default-схемы. Чтобы менять его настройки безопасно, продублируйте виджет и настройте копию.', true);
            return;
        }

        if (!widgetOptionsUrl || !window.fetch || !csrfToken || (parseInt(record.node.widget_id || 0, 10) || 0) < 1) {
            showWidgetOptionsMessage('Форма настроек для этого виджета сейчас недоступна.', false);
            return;
        }

        showWidgetOptionsMessage('Загружаю форму настроек виджета...', false);
        requestToken = widgetOptionsRequestToken + 1;
        widgetOptionsRequestToken = requestToken;

        body = serializeRequestBody({
            csrf_token: csrfToken,
            widget_id: record.node.widget_id || 0,
            template: page.template || '',
            options: JSON.stringify(record.node.bind_config || {})
        });

        window.fetch(widgetOptionsUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (widgetOptionsRequestToken !== requestToken) {
                return;
            }

            if (!response || response.error || !response.html) {
                showWidgetOptionsMessage((response && response.message) ? response.message : 'Не удалось загрузить форму настроек виджета.', false);
                return;
            }

            loadedWidgetOptionsUid = record.node.uid;
            renderWidgetOptionsForm(response.html);
        }).catch(function () {
            if (widgetOptionsRequestToken !== requestToken) {
                return;
            }

            showWidgetOptionsMessage('Не удалось загрузить форму настроек виджета.', false);
        });
    }

    function parseFieldNameTokens(name) {
        var tokens = [];

        if (name.indexOf('[') === -1 && name.indexOf(':') !== -1) {
            return name.split(':').filter(Boolean);
        }

        name.replace(/([^\[\]]+)|\[(.*?)\]/g, function (_, direct, bracket) {
            tokens.push(typeof direct !== 'undefined' && direct !== '' ? direct : String(typeof bracket === 'undefined' ? '' : bracket));
            return _;
        });

        return tokens.filter(function (token, index) {
            return token !== '' || index === tokens.length - 1;
        });
    }

    function setNestedValue(target, tokens, value) {
        var cursor = target;
        var index;
        var token;
        var nextToken;

        if (!tokens.length) {
            return;
        }

        for (index = 0; index < tokens.length; index += 1) {
            token = tokens[index];
            nextToken = tokens[index + 1];

            if (index === tokens.length - 1) {
                cursor[token] = value;
                return;
            }

            if (!cursor[token] || typeof cursor[token] !== 'object') {
                cursor[token] = nextToken === '' ? [] : {};
            }

            cursor = cursor[token];
        }
    }

    function appendNestedValue(target, tokens, value) {
        var parentTokens = tokens.slice(0, -1);
        var lastToken = tokens[tokens.length - 1];
        var parent = target;
        var index;

        if (!tokens.length) {
            return;
        }

        for (index = 0; index < parentTokens.length; index += 1) {
            if (!parent[parentTokens[index]] || typeof parent[parentTokens[index]] !== 'object') {
                parent[parentTokens[index]] = {};
            }

            parent = parent[parentTokens[index]];
        }

        if (lastToken === '') {
            if (!Array.isArray(parent)) {
                return;
            }

            parent.push(value);
            return;
        }

        if (!Array.isArray(parent[lastToken])) {
            parent[lastToken] = [];
        }

        parent[lastToken].push(value);
    }

    function serializeWidgetOptionsForm(form) {
        var data = {};
        var elements = Array.prototype.slice.call(form.elements || []).filter(function (element) {
            return element.name && !element.disabled && element.tagName !== 'BUTTON' && element.type !== 'submit';
        });
        var grouped = {};

        elements.forEach(function (element) {
            if (!grouped[element.name]) {
                grouped[element.name] = [];
            }

            grouped[element.name].push(element);
        });

        Object.keys(grouped).forEach(function (name) {
            var group = grouped[name];
            var first = group[0];
            var tokens = parseFieldNameTokens(name);

            if (first.type === 'checkbox') {
                if (group.length === 1 && tokens[tokens.length - 1] !== '') {
                    setNestedValue(data, tokens, first.checked ? (first.value || '1') : false);
                    return;
                }

                setNestedValue(data, tokens.slice(0, -1), []);
                group.forEach(function (element) {
                    if (element.checked) {
                        appendNestedValue(data, tokens, element.value || '1');
                    }
                });
                return;
            }

            if (first.type === 'radio') {
                setNestedValue(data, tokens, '');
                group.forEach(function (element) {
                    if (element.checked) {
                        setNestedValue(data, tokens, element.value || '');
                    }
                });
                return;
            }

            if (first.tagName === 'SELECT' && first.multiple) {
                setNestedValue(data, tokens, Array.prototype.slice.call(first.options).filter(function (option) {
                    return option.selected;
                }).map(function (option) {
                    return option.value;
                }));
                return;
            }

            setNestedValue(data, tokens, first.value);
        });

        return data;
    }

    function createEmptyStyleRule(selector) {
        return normalizeStyleRule({
            id: 0,
            title: '',
            path: selector || '',
            styles: {},
            custom: {}
        });
    }

    function isDeviceAwareStylePayload(payload) {
        var keys;
        var hasKnownDevice = false;
        var hasKnownState = false;

        if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
            return false;
        }

        keys = Object.keys(payload);
        keys.forEach(function (key) {
            if (styleDevices.indexOf(String(key)) !== -1) {
                hasKnownDevice = true;
            }
            if (styleStates.indexOf(String(key)) !== -1) {
                hasKnownState = true;
            }
        });

        return hasKnownDevice && !hasKnownState;
    }

    function normalizeStyleBranches(payload, isCustom) {
        var normalized = {};

        if (!payload || typeof payload !== 'object' || Array.isArray(payload)) {
            return normalized;
        }

        if (isDeviceAwareStylePayload(payload)) {
            Object.keys(payload).forEach(function (deviceKey) {
                var states = payload[deviceKey];

                if (!states || typeof states !== 'object' || Array.isArray(states)) {
                    return;
                }

                Object.keys(states).forEach(function (stateKey) {
                    var branchValue = states[stateKey];

                    if (isCustom) {
                        if (typeof branchValue === 'string' && branchValue.trim() !== '') {
                            if (!normalized[deviceKey]) {
                                normalized[deviceKey] = {};
                            }
                            normalized[deviceKey][stateKey] = branchValue;
                        }
                        return;
                    }

                    if (branchValue && typeof branchValue === 'object' && !Array.isArray(branchValue)) {
                        if (!normalized[deviceKey]) {
                            normalized[deviceKey] = {};
                        }
                        normalized[deviceKey][stateKey] = deepClone(branchValue);
                    }
                });
            });

            return normalized;
        }

        Object.keys(payload).forEach(function (stateKey) {
            var value = payload[stateKey];

            if (isCustom) {
                if (typeof value === 'string' && value.trim() !== '') {
                    if (!normalized.base) {
                        normalized.base = {};
                    }
                    normalized.base[stateKey] = value;
                }
                return;
            }

            if (value && typeof value === 'object' && !Array.isArray(value)) {
                if (!normalized.base) {
                    normalized.base = {};
                }
                normalized.base[stateKey] = deepClone(value);
            }
        });

        return normalized;
    }

    function normalizeStyleRule(rule) {
        rule = rule || {};

        return {
            id: parseInt(rule.id || 0, 10) || 0,
            title: String(rule.title || ''),
            path: String(rule.path || ''),
            is_enabled: rule.is_enabled !== false,
            styles: normalizeStyleBranches(rule.styles || {}, false),
            custom: normalizeStyleBranches(rule.custom || {}, true)
        };
    }

    function ensureNodeMeta(node) {
        if (!node.meta || typeof node.meta !== 'object' || Array.isArray(node.meta)) {
            node.meta = {};
        }

        return node.meta;
    }

    function getNodeStyleTarget(node) {
        var meta = ensureNodeMeta(node);
        var target = meta.style_target;

        if (!target) {
            return {
                selector: '',
                source: 'builder',
                title: ''
            };
        }

        if (typeof target === 'string') {
            return {
                selector: String(target),
                source: 'manual',
                title: String(target)
            };
        }

        return {
            selector: String(target.selector || ''),
            source: String(target.source || 'builder'),
            title: String(target.title || target.selector || '')
        };
    }

    function setNodeStyleTarget(node, selector, source, title) {
        var meta = ensureNodeMeta(node);

        selector = String(selector || '').trim();
        if (!selector) {
            delete meta.style_target;
            return;
        }

        meta.style_target = {
            selector: selector,
            source: String(source || 'manual'),
            title: String(title || selector)
        };
    }

    function getActiveStyleDeviceKey() {
        return activeDevice === 'desktop' ? 'base' : activeDevice;
    }

    function getStyleBranchDeclarations(styles, deviceKey, stateKey) {
        if (!styles[deviceKey] || !styles[deviceKey][stateKey] || typeof styles[deviceKey][stateKey] !== 'object') {
            return {};
        }

        return deepClone(styles[deviceKey][stateKey]);
    }

    function getEditorStyleDeclarations(rule) {
        var deviceKey = getActiveStyleDeviceKey();
        var baseDeclarations = getStyleBranchDeclarations(rule.styles || {}, 'base', 'default');

        if (deviceKey === 'base') {
            return baseDeclarations;
        }

        return Object.assign({}, baseDeclarations, getStyleBranchDeclarations(rule.styles || {}, deviceKey, 'default'));
    }

    function setStyleBranchDeclarations(rule, deviceKey, stateKey, declarations) {
        if (!rule.styles || typeof rule.styles !== 'object' || Array.isArray(rule.styles)) {
            rule.styles = {};
        }

        if (!rule.styles[deviceKey]) {
            rule.styles[deviceKey] = {};
        }

        if (!declarations || !Object.keys(declarations).length) {
            delete rule.styles[deviceKey][stateKey];
            if (!Object.keys(rule.styles[deviceKey]).length) {
                delete rule.styles[deviceKey];
            }
            return;
        }

        rule.styles[deviceKey][stateKey] = declarations;
    }

    function normalizeDeclarationValue(propertyName, value) {
        value = String(value || '').trim();

        if (!value) {
            return '';
        }

        if (['font-size', 'padding', 'border-radius', 'border-width'].indexOf(propertyName) !== -1 && /^-?\d+(\.\d+)?$/.test(value)) {
            return value + 'px';
        }

        return value;
    }

    function collectStyleDeclarationsFromForm() {
        var declarations = {};

        styleFields.forEach(function (field) {
            var propertyName = String(field.getAttribute('data-style-field') || '');
            var value = normalizeDeclarationValue(propertyName, field.value);

            if (!propertyName || !value) {
                return;
            }

            declarations[propertyName] = value;
        });

        if ((declarations['border-width'] || declarations['border-color']) && !declarations['border-style']) {
            declarations['border-style'] = 'solid';
        }

        return declarations;
    }

    function applyStyleDeclarationsToForm(declarations) {
        styleFields.forEach(function (field) {
            var propertyName = String(field.getAttribute('data-style-field') || '');
            field.value = declarations && declarations[propertyName] ? String(declarations[propertyName]) : '';
        });
    }

    function updateStyleButtons(record, target) {
        var hasSelector = Boolean(target && target.selector);

        if (styleSaveButton) {
            styleSaveButton.disabled = !record || !hasSelector;
        }
        if (styleResetButton) {
            styleResetButton.disabled = !record;
        }
        if (styleOpenPickerButton) {
            styleOpenPickerButton.disabled = !record || !pickerFrameUrl;
        }
    }

    function updateStyleTargetMeta(target) {
        if (styleTargetSource) {
            styleTargetSource.textContent = styleSourceLabels[target.source] || target.source || 'manual';
        }

        if (styleTargetHint) {
            if (!target.selector) {
                styleTargetHint.textContent = 'У этого узла пока нет style target. Впишите selector вручную или возьмите его с live-страницы.';
            } else {
                styleTargetHint.textContent = 'Rule будет сохранено для selector ' + target.selector + ' в текущем runtime nordicstyl.';
            }
        }
    }

    function updateStyleScopeNote() {
        var deviceKey = getActiveStyleDeviceKey();

        if (!styleScopeNote) {
            return;
        }

        if (deviceKey === 'base') {
            styleScopeNote.textContent = 'Desktop пишет в ветку base/default. Это общая база для всех устройств.';
            return;
        }

        styleScopeNote.textContent = (deviceLabels[activeDevice] || activeDevice) + ' пишет в ветку ' + deviceKey + '/default. Если rule уже есть в base, здесь можно сделать отдельный override.';
    }

    function buildStyleInspectorKey(record, selector) {
        if (!record) {
            return '';
        }

        return [record.node.uid, normalizeDeviceKey(activeDevice), String(selector || '')].join('|');
    }

    function buildSelectorForState(selector, state) {
        var pseudoStates = {
            hover: ':hover',
            active: ':active',
            focus: ':focus',
            'focus-visible': ':focus-visible',
            visited: ':visited',
            before: '::before',
            after: '::after'
        };

        if (!state || state === 'default' || state === 'normal') {
            return selector;
        }

        return selector + (pseudoStates[state] || '');
    }

    function wrapDeviceCss(deviceKey, css) {
        var mediaMap = {
            mobile: '@media (max-width: 767.98px)',
            tablet: '@media (min-width: 768px) and (max-width: 991.98px)',
            desktop: '@media (min-width: 992px)'
        };

        css = String(css || '').trim();
        if (!css) {
            return '';
        }

        if (!mediaMap[deviceKey]) {
            return css + '\n';
        }

        return mediaMap[deviceKey] + '{\n' + css + '}\n';
    }

    function buildStyleRulePreviewCss(rule) {
        var css = '';
        var devices = [];

        if (!rule || !rule.path) {
            return '';
        }

        devices = styleDevices.filter(function (deviceKey) {
            return (rule.styles && rule.styles[deviceKey]) || (rule.custom && rule.custom[deviceKey]);
        });

        if (!devices.length) {
            devices = ['base'];
        }

        devices.forEach(function (deviceKey) {
            var deviceCss = '';
            var states = [];
            var stylesByState = rule.styles && rule.styles[deviceKey] ? rule.styles[deviceKey] : {};
            var customByState = rule.custom && rule.custom[deviceKey] ? rule.custom[deviceKey] : {};

            states = styleStates.filter(function (stateKey) {
                return stylesByState[stateKey] || customByState[stateKey];
            });

            if (!states.length) {
                states = ['default'];
            }

            states.forEach(function (stateKey) {
                var selector = buildSelectorForState(rule.path, stateKey);
                var declarations = stylesByState[stateKey] || {};
                var customCss = typeof customByState[stateKey] === 'string' ? customByState[stateKey].trim() : '';
                var blockCss = '';

                if (!selector) {
                    return;
                }

                Object.keys(declarations).forEach(function (propertyName) {
                    var value = String(declarations[propertyName] || '').trim();

                    if (!propertyName || !value) {
                        return;
                    }

                    blockCss += propertyName + ':' + value + ';';
                });

                if (customCss) {
                    blockCss += customCss.replace(/[\r\n]+/g, ' ');
                }

                if (!blockCss) {
                    return;
                }

                deviceCss += selector + '{' + blockCss + '}\n';
            });

            css += wrapDeviceCss(deviceKey, deviceCss);
        });

        return css;
    }

    function applyStylePreview() {
        var doc;
        var styleNode;
        var cssText;

        if (!stylePickerFrame || !stylePickerFrame.contentDocument) {
            return;
        }

        try {
            doc = stylePickerFrame.contentDocument;
            styleNode = doc.getElementById('nordic-builder-style-preview');
            if (!styleNode) {
                styleNode = doc.createElement('style');
                styleNode.id = 'nordic-builder-style-preview';
                doc.head.appendChild(styleNode);
            }

            cssText = buildStyleRulePreviewCss(currentStyleRule);
            styleNode.textContent = cssText;
        } catch (error) {
            return;
        }
    }

    function openStylePicker() {
        if (!pickerFrameUrl) {
            setStatus('Live picker для этой страницы сейчас недоступен.');
            return;
        }

        if (stylePickerFrame && !stylePickerFrame.getAttribute('src')) {
            stylePickerFrame.setAttribute('src', pickerFrameUrl);
        }

        if (stylePickerModal) {
            stylePickerModal.hidden = false;
        }

        applyStylePreview();
    }

    function closeStylePicker() {
        if (stylePickerModal) {
            stylePickerModal.hidden = true;
        }
    }

    function loadStyleRule(selector, record, inspectorKey) {
        var requestToken;
        var body;

        selector = String(selector || '').trim();

        if (!selector) {
            currentStyleRule = createEmptyStyleRule('');
            loadedStyleKey = inspectorKey;
            applyStyleDeclarationsToForm({});
            applyStylePreview();
            return;
        }

        if (!styleRuleUrl || !window.fetch || !csrfToken) {
            currentStyleRule = createEmptyStyleRule(selector);
            loadedStyleKey = inspectorKey;
            applyStyleDeclarationsToForm(getEditorStyleDeclarations(currentStyleRule));
            applyStylePreview();
            return;
        }

        requestToken = styleRuleRequestToken + 1;
        styleRuleRequestToken = requestToken;
        currentStyleRule = createEmptyStyleRule(selector);
        applyStyleDeclarationsToForm(getEditorStyleDeclarations(currentStyleRule));
        applyStylePreview();

        body = serializeRequestBody({
            csrf_token: csrfToken,
            mode: 'load',
            selector: selector
        });

        window.fetch(styleRuleUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (styleRuleRequestToken !== requestToken || buildStyleInspectorKey(getSelectedRecord(), selector) !== inspectorKey) {
                return;
            }

            currentStyleRule = normalizeStyleRule(response && response.rule ? response.rule : createEmptyStyleRule(selector));
            if (!currentStyleRule.path) {
                currentStyleRule.path = selector;
            }

            loadedStyleKey = inspectorKey;
            applyStyleDeclarationsToForm(getEditorStyleDeclarations(currentStyleRule));
            applyStylePreview();

            if (styleTargetHint) {
                styleTargetHint.textContent = response && response.rule
                    ? 'Найдено существующее rule. Можно сразу править и сохранять.'
                    : 'Нового rule пока нет. Первое сохранение создаст его в runtime nordicstyl.';
            }
        }).catch(function () {
            if (styleRuleRequestToken !== requestToken) {
                return;
            }

            currentStyleRule = createEmptyStyleRule(selector);
            loadedStyleKey = inspectorKey;
            applyStyleDeclarationsToForm(getEditorStyleDeclarations(currentStyleRule));
            applyStylePreview();
            setStatus('Загрузить style rule сейчас не удалось. Можно продолжить и сохранить заново.');
        });
    }

    function syncStyleInspector(record) {
        var target;
        var inspectorKey;

        if (!styleSection) {
            return;
        }

        updateStyleScopeNote();

        if (!record) {
            currentStyleRule = createEmptyStyleRule('');
            loadedStyleKey = '';
            if (styleSelectorInput) {
                styleSelectorInput.value = '';
            }
            applyStyleDeclarationsToForm({});
            updateStyleTargetMeta({ selector: '', source: 'builder' });
            updateStyleButtons(null, { selector: '' });
            applyStylePreview();
            return;
        }

        target = getNodeStyleTarget(record.node);
        if (styleSelectorInput && styleSelectorInput.value !== target.selector) {
            styleSelectorInput.value = target.selector;
        }

        updateStyleTargetMeta(target);
        updateStyleButtons(record, target);
        inspectorKey = buildStyleInspectorKey(record, target.selector);

        if (!target.selector) {
            currentStyleRule = createEmptyStyleRule('');
            loadedStyleKey = inspectorKey;
            applyStyleDeclarationsToForm({});
            applyStylePreview();
            return;
        }

        if (currentStyleRule.path === target.selector && loadedStyleKey !== inspectorKey) {
            loadedStyleKey = inspectorKey;
            applyStyleDeclarationsToForm(getEditorStyleDeclarations(currentStyleRule));
            applyStylePreview();
            return;
        }

        if (loadedStyleKey === inspectorKey) {
            return;
        }

        loadStyleRule(target.selector, record, inspectorKey);
    }

    function updateCurrentStyleDraftFromForm() {
        if (!currentStyleRule.path && styleSelectorInput) {
            currentStyleRule.path = String(styleSelectorInput.value || '').trim();
        }

        setStyleBranchDeclarations(currentStyleRule, getActiveStyleDeviceKey(), 'default', collectStyleDeclarationsFromForm());
    }

    function saveCurrentStyleRule() {
        var record = getSelectedRecord();
        var selector;
        var body;

        if (!record) {
            setStatus('Сначала выберите узел, для которого сохраняется style rule.');
            return;
        }

        selector = styleSelectorInput ? String(styleSelectorInput.value || '').trim() : '';
        if (!selector) {
            setStatus('Сначала укажите selector target.');
            return;
        }

        if (!styleRuleUrl || !window.fetch || !csrfToken) {
            setStatus('Сохранение style rule сейчас недоступно.');
            return;
        }

        setNodeStyleTarget(record.node, selector, getNodeStyleTarget(record.node).source || 'manual', selector);
        currentStyleRule.path = selector;
        currentStyleRule.title = 'Builder · ' + String(record.node.title || defaultTitle(record.type));
        updateCurrentStyleDraftFromForm();
        persistCurrentRows();
        applyStylePreview();

        body = serializeRequestBody({
            csrf_token: csrfToken,
            mode: 'save',
            selector: currentStyleRule.path,
            title: currentStyleRule.title,
            rule_id: currentStyleRule.id || 0,
            styles: JSON.stringify(currentStyleRule.styles || {}),
            custom: JSON.stringify(currentStyleRule.custom || {})
        });

        window.fetch(styleRuleUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.error) {
                setStatus((response && response.message) ? response.message : 'Не удалось сохранить style rule.');
                return;
            }

            currentStyleRule = normalizeStyleRule(response.rule || currentStyleRule);
            loadedStyleKey = buildStyleInspectorKey(record, selector);
            applyStyleDeclarationsToForm(getEditorStyleDeclarations(currentStyleRule));
            applyStylePreview();
            setStatus(response.message || 'Style rule сохранено.');
        }).catch(function () {
            setStatus('Сохранение style rule сейчас недоступно.');
        });
    }

    function applyStyleTarget(selector, source) {
        var record = getSelectedRecord();

        selector = String(selector || '').trim();
        if (!record) {
            return;
        }

        setNodeStyleTarget(record.node, selector, source || 'manual', selector);
        if (styleSelectorInput) {
            styleSelectorInput.value = selector;
        }

        persistCurrentRows();
        loadedStyleKey = '';
        syncStyleInspector(record);
        setStatus(selector ? ('Style target назначен: ' + selector + '.') : 'Style target очищен для выбранного узла.');
    }

    function syncInspectorPanels(record) {
        updateSelectedLibrarySummary();

        if (columnInsertPanel) {
            columnInsertPanel.hidden = !record || record.type !== 'column';
        }

        if (!widgetOptionsBlock) {
            return;
        }

        widgetOptionsBlock.hidden = !record || record.type !== 'widget';

        if (!record || record.type !== 'widget') {
            showWidgetOptionsMessage('Выберите виджет, чтобы открыть его настройки.', false);
            return;
        }

        if (!record.node.can_edit_options) {
            showWidgetOptionsMessage('Этот виджет пришел из default-схемы. Чтобы менять его настройки безопасно, продублируйте виджет и настройте копию.', true);
            return;
        }

        if (loadedWidgetOptionsUid === record.node.uid && widgetOptionsBody && widgetOptionsBody.querySelector('[data-widget-options-form]')) {
            widgetOptionsBody.hidden = false;
            if (widgetOptionsEmpty) {
                widgetOptionsEmpty.hidden = true;
            }
            if (widgetOptionsLock) {
                widgetOptionsLock.hidden = true;
            }
            return;
        }

        loadWidgetOptions(record);
    }

    function syncDeviceButtons() {
        deviceButtons.forEach(function (button) {
            button.classList.toggle('is-active', String(button.getAttribute('data-device-key') || 'desktop') === activeDevice);
        });
    }

    function updateDeviceLabel() {
        if (currentDeviceLabel) {
            currentDeviceLabel.textContent = deviceLabels[activeDevice] || 'Desktop';
        }

        if (viewportDeviceNode) {
            viewportDeviceNode.textContent = deviceLabels[activeDevice] || 'Desktop';
        }
    }

    function updateViewportState() {
        var source = getDevicePreviewSource(activeDevice);

        if (canvas) {
            canvas.setAttribute('data-active-device', activeDevice);
            canvas.setAttribute('data-device-source', source);
        }

        if (viewportSourceNode) {
            viewportSourceNode.textContent = source === 'base' ? 'Base' : (source === 'override' ? 'Override' : 'Inherited');
        }

        if (viewportSubtitleNode) {
            if (source === 'inherited') {
                viewportSubtitleNode.textContent = 'Сейчас используется унаследованная версия от предыдущего устройства. Первое изменение создаст override.';
            } else if (source === 'override') {
                viewportSubtitleNode.textContent = 'Для этого устройства уже есть свой layout override. Canvas показывает именно его.';
            } else {
                viewportSubtitleNode.textContent = 'Desktop остается базовой live-схемой страницы.';
            }
        }
    }

    function updateDeviceUrl() {
        if (!window.history || !window.history.replaceState || !window.URL) {
            return;
        }

        try {
            var url = new URL(window.location.href);
            if (activeDevice === 'desktop') {
                url.searchParams.delete('device');
            } else {
                url.searchParams.set('device', activeDevice);
            }
            window.history.replaceState({}, '', url.toString());
        } catch (error) {
            return;
        }
    }

    function findNodeRecord(rows, uid) {
        var rowIndex;
        var columnIndex;
        var widgetIndex;
        var nestedRecord;

        for (rowIndex = 0; rowIndex < rows.length; rowIndex += 1) {
            if (rows[rowIndex].uid === uid) {
                return {
                    node: rows[rowIndex],
                    type: 'row',
                    parentCollection: rows,
                    index: rowIndex
                };
            }

            for (columnIndex = 0; columnIndex < rows[rowIndex].columns.length; columnIndex += 1) {
                if (rows[rowIndex].columns[columnIndex].uid === uid) {
                    return {
                        node: rows[rowIndex].columns[columnIndex],
                        type: 'column',
                        parentCollection: rows[rowIndex].columns,
                        index: columnIndex
                    };
                }

                for (widgetIndex = 0; widgetIndex < rows[rowIndex].columns[columnIndex].widgets.length; widgetIndex += 1) {
                    if (rows[rowIndex].columns[columnIndex].widgets[widgetIndex].uid === uid) {
                        return {
                            node: rows[rowIndex].columns[columnIndex].widgets[widgetIndex],
                            type: 'widget',
                            parentCollection: rows[rowIndex].columns[columnIndex].widgets,
                            index: widgetIndex
                        };
                    }
                }

                nestedRecord = findNodeRecord(rows[rowIndex].columns[columnIndex].nested_rows, uid);
                if (nestedRecord) {
                    return nestedRecord;
                }
            }
        }

        return null;
    }

    function getSelectedRecord() {
        if (!selectedNodeUid) {
            return null;
        }

        return findNodeRecord(currentRows, selectedNodeUid);
    }

    function applySelection() {
        if (!stage) {
            return;
        }

        stage.querySelectorAll('.is-selected').forEach(function (node) {
            node.classList.remove('is-selected');
        });

        if (!selectedNodeUid) {
            return;
        }

        var selectedNode = stage.querySelector('[data-node-uid="' + selectedNodeUid + '"]');
        if (!selectedNode) {
            selectedNodeUid = null;
            return;
        }

        selectedNode.classList.add('is-selected');
    }

    function updateInspector() {
        var record = getSelectedRecord();
        var width = '—';

        if (!record) {
            if (inspectorEmpty) {
                inspectorEmpty.hidden = false;
            }
            if (inspectorMeta) {
                inspectorMeta.hidden = true;
            }
            if (settingsEmpty) {
                settingsEmpty.hidden = false;
            }
            if (settingsPanel) {
                settingsPanel.hidden = true;
            }
            syncInspectorPanels(null);
            syncStyleInspector(null);
            return;
        }

        if (record.type === 'column') {
            width = String(record.node.width || 12) + '/12';
        }

        if (inspectorEmpty) {
            inspectorEmpty.hidden = true;
        }
        if (inspectorMeta) {
            inspectorMeta.hidden = false;
        }
        if (inspectorType) {
            inspectorType.textContent = record.type;
        }
        if (inspectorTitle) {
            inspectorTitle.textContent = record.node.title || defaultTitle(record.type);
        }
        if (inspectorWidth) {
            inspectorWidth.textContent = width;
        }

        if (settingsEmpty) {
            settingsEmpty.hidden = true;
        }
        if (settingsPanel) {
            settingsPanel.hidden = false;
        }

        if (titleInput) {
            titleInput.value = record.node.title || defaultTitle(record.type);
        }

        if (rowModeField) {
            rowModeField.hidden = record.type !== 'row';
        }
        if (rowModeInput && record.type === 'row') {
            rowModeInput.value = record.node.width_mode || 'grid';
        }

        if (columnWidthField) {
            columnWidthField.hidden = record.type !== 'column';
        }
        if (columnWidthInput && record.type === 'column') {
            columnWidthInput.value = String(record.node.width || 12);
        }
        if (columnWidthValue) {
            columnWidthValue.textContent = record.type === 'column' ? String(record.node.width || 12) + '/12' : '—';
        }

        if (deviceOverrideNote) {
            if (activeDevice === 'desktop') {
                deviceOverrideNote.textContent = 'Desktop — базовая snapshot-схема страницы.';
            } else if (hasOverride(activeDevice)) {
                deviceOverrideNote.textContent = 'Для ' + (deviceLabels[activeDevice] || activeDevice) + ' уже есть свой override.';
            } else {
                deviceOverrideNote.textContent = 'Сейчас используется наследование от ' + (deviceLabels[getParentDevice(activeDevice)] || 'Desktop') + '. Первое изменение создаст override.';
            }
        }

        syncInspectorPanels(record);
        syncStyleInspector(record);
    }

    function updateColumnDomWidth(uid, width) {
        var columnNode = stage ? stage.querySelector('[data-node-uid="' + uid + '"]') : null;
        var widthLabel;

        if (!columnNode) {
            return;
        }

        columnNode.setAttribute('data-units', String(width));
        columnNode.style.setProperty('--nb-col-span', String(width));
        widthLabel = columnNode.querySelector('[data-column-width-label]');
        if (widthLabel) {
            widthLabel.textContent = String(width) + '/12';
        }
    }

    function selectNode(uid) {
        selectedNodeUid = uid;
        applySelection();
        updateInspector();

        var record = getSelectedRecord();
        if (record) {
            setStatus('Выбран узел: ' + (record.node.title || defaultTitle(record.type)) + '.');
        }
    }

    function rerender() {
        renderCanvas();
        renderOutline();
        updateViewportState();
        applySelection();
        updateInspector();
        syncDeviceButtons();
        updateDeviceLabel();
        updateSelectedLibrarySummary();
        applyWidgetFilter(currentWidgetFilter);
    }

    function renumberTree(node, type) {
        node.uid = makeUid(type);
        node.hidden = false;

        if (type === 'row') {
            node.columns = (node.columns || []).map(function (column) {
                renumberTree(column, 'column');
                return column;
            });
        }

        if (type === 'column') {
            node.widgets = (node.widgets || []).map(function (widget) {
                renumberTree(widget, 'widget');
                return widget;
            });
            node.nested_rows = (node.nested_rows || []).map(function (row) {
                renumberTree(row, 'row');
                return row;
            });
        }

        if (type === 'widget') {
            node.bind_id = 0;
            node.binding_page_id = 0;
            node.source_page_id = 0;
        }
    }

    function duplicateNode(uid) {
        var record = findNodeRecord(currentRows, uid);
        var clone;

        if (!record) {
            return;
        }

        if (record.type === 'row') {
            clone = normalizeRow(deepClone(record.node));
        } else if (record.type === 'column') {
            clone = normalizeColumn(deepClone(record.node));
        } else {
            clone = normalizeWidget(deepClone(record.node));
        }

        renumberTree(clone, record.type);
        clone.title = duplicateTitle(clone.title || defaultTitle(record.type));
        record.parentCollection.splice(record.index + 1, 0, clone);
        selectedNodeUid = clone.uid;
        persistCurrentRows();
        rerender();
        setStatus('Узел продублирован в режиме ' + (deviceLabels[activeDevice] || activeDevice) + '.');
    }

    function addRowAfter(uid) {
        var record = uid ? findNodeRecord(currentRows, uid) : null;
        var row = createDefaultRow();

        if (record && record.type === 'row') {
            record.parentCollection.splice(record.index + 1, 0, row);
        } else {
            currentRows.push(row);
        }

        selectedNodeUid = row.uid;
        persistCurrentRows();
        rerender();
        setStatus('Новый ряд добавлен и сохранен на сервере.');
    }

    function addSectionToColumn(uid) {
        var record = findNodeRecord(currentRows, uid);
        var row;

        if (!record || record.type !== 'column') {
            return;
        }

        row = createDefaultRow('Новая секция');
        record.node.nested_rows.push(row);
        selectedNodeUid = row.uid;
        persistCurrentRows();
        rerender();
        setStatus('Секция добавлена внутрь колонки и сохранена на сервере.');
    }

    function addWidgetToColumn(uid) {
        var record = findNodeRecord(currentRows, uid);
        var widget;

        if (!record || record.type !== 'column') {
            return;
        }

        if (!selectedLibraryCard) {
            selectedNodeUid = record.node.uid;
            rerender();
            setStatus('Колонка выбрана. Сначала выберите виджет слева и нажмите «Вставить выбранный виджет», либо добавьте секцию.');
            return;
        }

        widget = buildWidgetFromLibraryCard(selectedLibraryCard);
        record.node.widgets.push(widget);
        selectedNodeUid = widget.uid;
        persistCurrentRows();
        rerender();
        setStatus('Виджет добавлен в колонку и сохранен на сервере.');
    }

    function toggleNodeHidden(uid) {
        var record = findNodeRecord(currentRows, uid);

        if (!record) {
            return;
        }

        record.node.hidden = !record.node.hidden;
        persistCurrentRows();
        selectedNodeUid = uid;
        rerender();
        setStatus(record.node.hidden ? 'Узел скрыт для текущего режима.' : 'Узел снова показан в текущем режиме.');
    }

    function deleteNode(uid) {
        var record = findNodeRecord(currentRows, uid);

        if (!record) {
            return;
        }

        record.parentCollection.splice(record.index, 1);
        if (selectedNodeUid === uid) {
            selectedNodeUid = null;
        }
        persistCurrentRows();
        rerender();
        setStatus('Узел удален из текущего режима.');
    }

    function applyWidgetFilter(filterValue) {
        currentWidgetFilter = filterValue || 'all';

        widgetLibraryItems.forEach(function (item) {
            var category = String(item.getAttribute('data-widget-category') || 'all');
            item.hidden = currentWidgetFilter !== 'all' && category !== currentWidgetFilter;
        });

        widgetFilterButtons.forEach(function (button) {
            button.classList.toggle('is-active', String(button.getAttribute('data-widget-filter') || 'all') === currentWidgetFilter);
        });
    }

    function switchDevice(deviceKey) {
        activeDevice = normalizeDeviceKey(deviceKey);
        currentRows = resolveRowsForDevice(activeDevice);
        updateDeviceUrl();
        rerender();
        setStatus('Переключен режим: ' + (deviceLabels[activeDevice] || activeDevice) + '.');
    }

    root.addEventListener('click', function (event) {
        var filterButton = event.target.closest('[data-widget-filter]');
        var libraryCard = event.target.closest('[data-widget-library-item]');
        var insertSelectedWidgetAction = event.target.closest('[data-insert-selected-widget]');
        var insertSectionAction = event.target.closest('[data-insert-section]');
        var actionButton = event.target.closest('[data-node-action]');
        var addButton = event.target.closest('[data-builder-add]');
        var outlineTarget = event.target.closest('[data-select-target]');
        var deviceButton = event.target.closest('[data-device-button]');
        var restoreButton = event.target.closest('[data-history-restore]');
        var node = event.target.closest('[data-builder-node]');
        var uid;

        if (filterButton) {
            event.preventDefault();
            applyWidgetFilter(String(filterButton.getAttribute('data-widget-filter') || 'all'));
            return;
        }

        if (libraryCard) {
            event.preventDefault();
            root.querySelectorAll('.nb-library-card.is-selected').forEach(function (selectedCard) {
                selectedCard.classList.remove('is-selected');
            });
            libraryCard.classList.add('is-selected');
            selectedLibraryCard = libraryCard;
            updateSelectedLibrarySummary();
            setStatus('Выбран виджет из библиотеки: ' + String(libraryCard.getAttribute('data-widget-title') || 'виджет') + '.');
            return;
        }

        if (insertSelectedWidgetAction) {
            event.preventDefault();

            if (!selectedNodeUid) {
                setStatus('Сначала выберите колонку, в которую нужно вставить виджет.');
                return;
            }

            addWidgetToColumn(selectedNodeUid);
            return;
        }

        if (insertSectionAction) {
            event.preventDefault();

            if (!selectedNodeUid) {
                setStatus('Сначала выберите колонку, в которую нужно добавить секцию.');
                return;
            }

            addSectionToColumn(selectedNodeUid);
            return;
        }

        if (actionButton) {
            event.preventDefault();
            uid = String(actionButton.closest('[data-builder-node]').getAttribute('data-node-uid') || '');

            if (actionButton.getAttribute('data-node-action') === 'settings') {
                selectNode(uid);
                if (titleInput) {
                    titleInput.focus();
                    titleInput.select();
                }
                return;
            }
            if (actionButton.getAttribute('data-node-action') === 'duplicate') {
                duplicateNode(uid);
                return;
            }
            if (actionButton.getAttribute('data-node-action') === 'hide') {
                toggleNodeHidden(uid);
                return;
            }
            if (actionButton.getAttribute('data-node-action') === 'delete') {
                deleteNode(uid);
            }
            return;
        }

        if (addButton) {
            var addKind = String(addButton.getAttribute('data-add-kind') || '');
            var rowNode = addButton.closest('[data-node-type="row"]');
            var columnNode = addButton.closest('[data-node-type="column"]');

            event.preventDefault();

            if (addKind === 'row') {
                addRowAfter(rowNode ? String(rowNode.getAttribute('data-node-uid') || '') : '');
                return;
            }

            if (addKind === 'widget') {
                if (!columnNode) {
                    setStatus('Не удалось определить колонку для вставки.');
                    return;
                }

                addWidgetToColumn(String(columnNode.getAttribute('data-node-uid') || ''));
                return;
            }

            setStatus('Тип вставки пока не поддержан.');
            return;
        }

        if (outlineTarget) {
            event.preventDefault();
            selectNode(String(outlineTarget.getAttribute('data-select-target') || ''));
            return;
        }

        if (deviceButton) {
            event.preventDefault();
            switchDevice(String(deviceButton.getAttribute('data-device-key') || 'desktop'));
            return;
        }

        if (restoreButton) {
            event.preventDefault();
            restoreRevision(parseInt(restoreButton.getAttribute('data-revision-id') || '0', 10) || 0);
            return;
        }

        if (publishButton && event.target.closest('[data-builder-publish]')) {
            event.preventDefault();
            publishDesktopLayout();
            return;
        }

        if (resetButton && event.target.closest('[data-builder-reset]')) {
            event.preventDefault();
            resetTemplateToDefault();
            return;
        }

        if (node && root.contains(node)) {
            selectNode(String(node.getAttribute('data-node-uid') || ''));
        }
    });

    root.addEventListener('change', function (event) {
        var form = event.target.closest('[data-widget-options-form]');
        var record;
        var bindConfig;

        if (!form) {
            return;
        }

        record = getSelectedRecord();
        if (!record || record.type !== 'widget') {
            return;
        }

        bindConfig = normalizeWidgetBindConfig(serializeWidgetOptionsForm(form), record.node);
        record.node.bind_config = bindConfig;
        record.node.title = bindConfig.title;

        if (titleInput) {
            titleInput.value = record.node.title;
        }

        persistCurrentRows();
        rerender();
        setStatus('Настройки виджета обновлены в редакторе. Чтобы применить их на сайт, нажмите «Сохранить».');
    });

    root.addEventListener('mousedown', function (event) {
        var handle = event.target.closest('[data-resize-handle]');
        var leftRecord;
        var rightRecord;

        if (!handle || window.matchMedia('(max-width: 760px)').matches) {
            return;
        }

        leftRecord = findNodeRecord(currentRows, String(handle.getAttribute('data-left-uid') || ''));
        rightRecord = findNodeRecord(currentRows, String(handle.getAttribute('data-right-uid') || ''));

        if (!leftRecord || !rightRecord || leftRecord.type !== 'column' || rightRecord.type !== 'column') {
            return;
        }

        event.preventDefault();

        dragState = {
            rowRect: handle.parentNode.getBoundingClientRect(),
            leftUid: leftRecord.node.uid,
            rightUid: rightRecord.node.uid,
            totalUnits: (leftRecord.node.width || 6) + (rightRecord.node.width || 6)
        };

        document.body.classList.add('nb-builder-is-dragging');
        setStatus('Измени ширину колонок мышкой. Override сохранится в текущем режиме.');
    });

    document.addEventListener('mousemove', function (event) {
        var ratio;
        var newLeftUnits;
        var newRightUnits;
        var leftRecord;
        var rightRecord;

        if (!dragState) {
            return;
        }

        ratio = (event.clientX - dragState.rowRect.left) / dragState.rowRect.width;
        newLeftUnits = clampUnits(Math.round(ratio * 12));
        newRightUnits = dragState.totalUnits - newLeftUnits;

        if (newRightUnits < 2) {
            newRightUnits = 2;
            newLeftUnits = dragState.totalUnits - newRightUnits;
        }

        leftRecord = findNodeRecord(currentRows, dragState.leftUid);
        rightRecord = findNodeRecord(currentRows, dragState.rightUid);

        if (!leftRecord || !rightRecord) {
            return;
        }

        leftRecord.node.width = newLeftUnits;
        rightRecord.node.width = newRightUnits;
        updateColumnDomWidth(leftRecord.node.uid, newLeftUnits);
        updateColumnDomWidth(rightRecord.node.uid, newRightUnits);

        if (selectedNodeUid === leftRecord.node.uid || selectedNodeUid === rightRecord.node.uid) {
            updateInspector();
        }
    });

    document.addEventListener('mouseup', function () {
        if (!dragState) {
            return;
        }

        persistCurrentRows();
        dragState = null;
        document.body.classList.remove('nb-builder-is-dragging');
        rerender();
        setStatus('Ширина колонок обновлена для режима ' + (deviceLabels[activeDevice] || activeDevice) + '.');
    });

    if (pageSwitcher) {
        pageSwitcher.addEventListener('change', function () {
            var option = pageSwitcher.options[pageSwitcher.selectedIndex];
            var nextUrl = pageSwitcher.value;

            if (!nextUrl) {
                return;
            }

            setStatus(option ? 'Переключаю builder на контекст: ' + option.textContent : 'Переключаю контекст страницы.');
            window.location.assign(nextUrl);
        });
    }

    if (titleInput) {
        titleInput.addEventListener('change', function () {
            var record = getSelectedRecord();

            if (!record) {
                return;
            }

            record.node.title = String(titleInput.value || '').trim() || defaultTitle(record.type);
            syncWidgetTitleIntoBindConfig(record.node);
            syncWidgetOptionsFormTitle(record.node.title);
            persistCurrentRows();
            rerender();
            setStatus('Название узла обновлено.');
        });
    }

    if (rowModeInput) {
        rowModeInput.addEventListener('change', function () {
            var record = getSelectedRecord();

            if (!record || record.type !== 'row') {
                return;
            }

            record.node.width_mode = rowModeInput.value === 'full' ? 'full' : 'grid';
            persistCurrentRows();
            rerender();
            setStatus('Режим ряда обновлен для текущего устройства.');
        });
    }

    if (columnWidthInput) {
        columnWidthInput.addEventListener('input', function () {
            var record = getSelectedRecord();
            var width;

            if (!record || record.type !== 'column') {
                return;
            }

            width = clampUnits(parseInt(columnWidthInput.value || '12', 10) || 12);
            record.node.width = width;
            if (columnWidthValue) {
                columnWidthValue.textContent = String(width) + '/12';
            }
            if (inspectorWidth) {
                inspectorWidth.textContent = String(width) + '/12';
            }
            updateColumnDomWidth(record.node.uid, width);
        });

        columnWidthInput.addEventListener('change', function () {
            persistCurrentRows();
            rerender();
            setStatus('Ширина колонки обновлена для текущего устройства.');
        });
    }

    if (styleSelectorInput) {
        styleSelectorInput.addEventListener('change', function () {
            applyStyleTarget(styleSelectorInput.value, 'manual');
        });
    }

    styleFields.forEach(function (field) {
        field.addEventListener('input', function () {
            updateCurrentStyleDraftFromForm();
            applyStylePreview();
        });
    });

    if (styleSaveButton) {
        styleSaveButton.addEventListener('click', function () {
            saveCurrentStyleRule();
        });
    }

    if (styleResetButton) {
        styleResetButton.addEventListener('click', function () {
            applyStyleDeclarationsToForm({});
            updateCurrentStyleDraftFromForm();
            applyStylePreview();
            setStatus('Поля style inspector очищены. Нажмите «Сохранить style rule», если это нужно записать в runtime.');
        });
    }

    if (styleOpenPickerButton) {
        styleOpenPickerButton.addEventListener('click', function () {
            openStylePicker();
        });
    }

    stylePickerCloseButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            closeStylePicker();
        });
    });

    if (stylePickerFrame) {
        stylePickerFrame.addEventListener('load', function () {
            applyStylePreview();
        });
    }

    window.addEventListener('message', function (event) {
        var data = event.data || {};

        if (pickerOrigin && event.origin !== pickerOrigin) {
            return;
        }

        if (!data || data.type !== 'nordicstyl-picker') {
            return;
        }

        applyStyleTarget(String(data.selector || ''), 'picker');
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && stylePickerModal && !stylePickerModal.hidden) {
            closeStylePicker();
        }
    });

    rerender();
    applyWidgetFilter('all');
    updateSelectedLibrarySummary();
    if (currentRows.length) {
        selectNode(currentRows[0].uid);
    }
})();
