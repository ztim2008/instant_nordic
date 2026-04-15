(function () {
    var root = document.querySelector('[data-nordic-live]');
    var state = window.NORDIC_LIVE_STATE || {};
    var page = state.page || {};
    var pageSwitcher = root ? root.querySelector('[data-live-page-switcher]') : null;
    var deviceButtons = root ? root.querySelectorAll('[data-live-device-button]') : [];
    var presetButtons = root ? root.querySelectorAll('[data-live-preset]') : [];
    var insertContextNode = root ? root.querySelector('[data-live-insert-context]') : null;
    var contentPanel = root ? root.querySelector('[data-live-content-panel]') : null;
    var contentForm = root ? root.querySelector('[data-live-content-form]') : null;
    var contentHint = root ? root.querySelector('[data-live-content-hint]') : null;
    var contentSaveButton = root ? root.querySelector('[data-live-content-save]') : null;
    var contentFields = {};
    var frame = root ? root.querySelector('[data-live-frame]') : null;
    var statusNode = root ? root.querySelector('[data-live-frame-status]') : null;
    var statusStrip = root ? root.querySelector('[data-live-status-strip]') : null;
    var frameShell = root ? root.querySelector('[data-live-frame-shell]') : null;
    var frameOverlay = root ? root.querySelector('[data-live-frame-overlay]') : null;
    var currentUriNodes = root ? root.querySelectorAll('[data-live-current-uri], [data-live-current-uri-duplicate]') : [];
    var currentDeviceNode = root ? root.querySelector('[data-live-current-device]') : null;
    var editModeNode = root ? root.querySelector('[data-live-edit-mode]') : null;
    var targetSourceNode = root ? root.querySelector('[data-live-target-source]') : null;
    var selectedTitleNode = root ? root.querySelector('[data-live-selected-title]') : null;
    var selectorNode = root ? root.querySelector('[data-live-selected-selector]') : null;
    var layoutState = normalizeLayoutState(state.layout_state || {});
    var saveStateUrl = String(state.state_url || '');
    var csrfToken = String(state.csrf_token || '');
    var baseFrameUrl = String(state.picker_frame_url || (frame ? frame.getAttribute('src') : '') || '');
    var selectedTargetKey = '';
    var selectedTargetTitle = '';

    Array.prototype.forEach.call(root.querySelectorAll('[data-live-content-field]'), function (node) {
        contentFields[String(node.getAttribute('data-live-content-field') || '').trim()] = node;
    });

    if (!root) {
        return;
    }

    function deepClone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function serializeBody(payload) {
        return Object.keys(payload).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(String(payload[key]));
        }).join('&');
    }

    function makeUid(prefix) {
        return prefix + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
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
                text: 'Ниже можно быстро поменять список преимуществ без ухода в отдельный редактор.',
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

    function normalizeLayoutState(rawState) {
        var normalized = rawState && typeof rawState === 'object' ? deepClone(rawState) : {};

        if (!normalized.desktop || typeof normalized.desktop !== 'object') {
            normalized.desktop = {};
        }

        if (!Array.isArray(normalized.desktop.rows)) {
            normalized.desktop.rows = [];
        }

        return normalized;
    }

    function setStatus(text, tone) {
        if (statusNode) {
            statusNode.textContent = text;
        }

        if (statusStrip) {
            statusStrip.setAttribute('data-tone', tone || 'muted');
        }
    }

    function setFrameState(nextState) {
        if (frameShell) {
            frameShell.setAttribute('data-frame-state', nextState || 'ready');
        }
    }

    function setOverlay(title, text) {
        var titleNode;
        var textNode;

        if (!frameOverlay) {
            return;
        }

        titleNode = frameOverlay.querySelector('.nlive__frame-overlay-title');
        textNode = frameOverlay.querySelector('.nlive__frame-overlay-text');

        if (titleNode) {
            titleNode.textContent = title || '';
        }

        if (textNode) {
            textNode.textContent = text || '';
        }
    }

    function setCurrentUri(text) {
        Array.prototype.forEach.call(currentUriNodes, function (node) {
            node.textContent = text;
        });
    }

    function setSelectedSelector(text) {
        if (selectorNode) {
            selectorNode.textContent = text || 'Еще не выбран';
        }
    }

    function setSelectedTitle(text) {
        if (selectedTitleNode) {
            selectedTitleNode.textContent = text || 'Еще не выбран';
        }
    }

    function setEditMode(text) {
        if (editModeNode) {
            editModeNode.textContent = text || 'Desktop · Обычный';
        }
    }

    function setTargetSource(text) {
        if (targetSourceNode) {
            targetSourceNode.textContent = text || 'Ждем выбор элемента';
        }
    }

    function updateInsertContext() {
        var record = findNodeRecord(layoutState.desktop.rows, selectedTargetKey);
        var text = 'Точка вставки: в конец страницы.';

        if (record && record.kind === 'row') {
            text = 'Точка вставки: сразу после секции «' + record.title + '».';
        } else if (record && record.kind === 'column') {
            text = 'Точка вставки: внутрь колонки «' + record.title + '».';
        } else if (selectedTargetTitle) {
            text = 'Точка вставки: в конец основной страницы. Текущий выбор: «' + selectedTargetTitle + '».';
        }

        if (insertContextNode) {
            insertContextNode.textContent = text;
        }
    }

    function setContentHint(text) {
        if (contentHint) {
            contentHint.textContent = text;
        }
    }

    function setContentPanelVisible(isVisible) {
        if (contentForm) {
            contentForm.hidden = !isVisible;
        }
    }

    function setContentFieldVisibility(fieldName, isVisible) {
        var node = root.querySelector('[data-live-content-wrap="' + fieldName + '"]');

        if (node) {
            node.hidden = !isVisible;
        }
    }

    function buildFrameUrl(extraParams) {
        var url;
        var hash = '';
        var hashIndex;

        if (!baseFrameUrl) {
            return '';
        }

        url = baseFrameUrl;
        hashIndex = url.indexOf('#');

        if (hashIndex !== -1) {
            hash = url.slice(hashIndex);
            url = url.slice(0, hashIndex);
        }

        url = new URL(url, window.location.origin);

        Object.keys(extraParams || {}).forEach(function (key) {
            if (extraParams[key] === null || typeof extraParams[key] === 'undefined' || extraParams[key] === '') {
                url.searchParams.delete(key);
                return;
            }

            url.searchParams.set(key, extraParams[key]);
        });

        return url.toString() + hash;
    }

    function reloadFrame(focusKey) {
        if (!frame) {
            return;
        }

        setFrameState('loading');
        setOverlay('Обновляю live preview', 'Перезагружаю iframe с draft layout и перевожу фокус на новую секцию.');
        setStatus('Секция сохранена в draft. Обновляю live iframe без публикации на сайт.', 'muted');
        frame.src = buildFrameUrl({
            nordicstyl_focus: focusKey || '',
            nordicstyl_reload: String(Date.now())
        });
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

                result = findNodeRecord(column.nested_rows || [], targetKey);

                return Boolean(result);
            });
        });

        return result;
    }

    function getSelectedPresetContext() {
        var record = findNodeRecord(layoutState.desktop.rows, selectedTargetKey);
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

        setContentHint('Выбрана секция «' + (context.row.title || context.presetKey) + '». Меняйте текст ниже и сохраняйте в тот же draft layout.');
        setContentPanelVisible(true);
    }

    function updateContentInspector() {
        var context = getSelectedPresetContext();

        if (!contentPanel || !contentForm) {
            return;
        }

        if (!context) {
            setContentHint('Выберите вставленную draft-секцию, чтобы сразу поменять ее текст и снова увидеть результат в iframe.');
            setContentPanelVisible(false);
            return;
        }

        applyContentForm(context);
    }

    function buildContentPayload(context) {
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

    function saveSelectedPresetContent() {
        var context = getSelectedPresetContext();
        var snapshot = deepClone(layoutState);
        var nextTitle;

        if (!context) {
            setStatus('Сначала выберите draft-секцию в iframe или после вставки пресета.', 'warn');
            return;
        }

        nextTitle = contentFields.section_title ? String(contentFields.section_title.value || '').trim() : '';
        context.row.title = nextTitle || String(context.row.title || context.presetKey || 'Секция');

        if (!context.row.meta || typeof context.row.meta !== 'object') {
            context.row.meta = {};
        }

        context.row.meta.preset_key = context.presetKey;
        context.row.meta.content = buildContentPayload(context);

        selectedTargetKey = String(context.row.uid || '').trim();
        selectedTargetTitle = String(context.row.title || '').trim();
        setSelectedTitle(selectedTargetTitle || 'Секция');
        setSelectedSelector(selectedTargetKey ? ('node:' + selectedTargetKey) : 'Еще не выбран');
        updateInsertContext();
        updateContentInspector();
        setStatus('Сохраняю текст draft-секции и обновляю iframe…', 'muted');

        persistLayoutState().then(function (response) {
            if (!response || response.error) {
                layoutState = snapshot;
                updateInsertContext();
                updateContentInspector();
                setStatus(response && response.message ? response.message : 'Не удалось сохранить контент draft-секции.', 'warn');
                return;
            }

            reloadFrame(selectedTargetKey);
        }).catch(function () {
            layoutState = snapshot;
            updateInsertContext();
            updateContentInspector();
            setStatus('Сохранение контента draft-секции сейчас недоступно.', 'warn');
        });
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
        if (!saveStateUrl || !window.fetch || !csrfToken) {
            return Promise.reject(new Error('draft-save-unavailable'));
        }

        return window.fetch(saveStateUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeBody({
                csrf_token: csrfToken,
                template: String(page.template || ''),
                uri: String(page.uri_raw || '/'),
                layout_state: JSON.stringify(layoutState)
            })
        }).then(function (response) {
            return response.json();
        });
    }

    function insertPreset(button) {
        var presetKey = String(button.getAttribute('data-live-preset') || '').trim();
        var presetTitle = String(button.getAttribute('data-live-preset-title') || '').trim() || 'Секция';
        var snapshot = deepClone(layoutState);
        var rowToInsert;

        if (!presetKey) {
            return;
        }

        rowToInsert = createPresetRow(presetKey, presetTitle);
        insertPresetRow(rowToInsert);

        setStatus('Сохраняю draft layout для новой секции…', 'muted');

        persistLayoutState().then(function (response) {
            if (!response || response.error) {
                layoutState = snapshot;
                updateInsertContext();
                setStatus(response && response.message ? response.message : 'Не удалось сохранить draft layout.', 'warn');
                return;
            }

            selectedTargetKey = String(rowToInsert.uid || '');
            selectedTargetTitle = String(rowToInsert.title || presetTitle);
            setSelectedTitle(selectedTargetTitle);
            setSelectedSelector(selectedTargetKey ? ('node:' + selectedTargetKey) : '');
            updateInsertContext();
            reloadFrame(selectedTargetKey);
        }).catch(function () {
            layoutState = snapshot;
            updateInsertContext();
            setStatus('Сохранение draft layout сейчас недоступно.', 'warn');
        });
    }

    if (pageSwitcher) {
        pageSwitcher.addEventListener('change', function () {
            if (!pageSwitcher.value) {
                return;
            }

            setFrameState('loading');
            setOverlay('Переключаю страницу', 'Сейчас открою другой live-контекст и заново подготовлю iframe для выбора элемента.');
            setStatus('Переключаю live-дизайн на выбранную страницу.', 'muted');
            window.location.assign(pageSwitcher.value);
        });
    }

    Array.prototype.forEach.call(deviceButtons, function (button) {
        button.addEventListener('click', function () {
            var nextUrl = String(button.getAttribute('data-nav-url') || '');

            if (!nextUrl) {
                return;
            }

            setFrameState('loading');
            setOverlay('Переключаю размер экрана', 'Обновляю workspace под другой device mode. После загрузки iframe выбор элемента останется доступен.');
            setStatus('Переключаю live-дизайн на другой размер экрана.', 'muted');
            window.location.assign(nextUrl);
        });
    });

    Array.prototype.forEach.call(presetButtons, function (button) {
        button.addEventListener('click', function () {
            insertPreset(button);
        });
    });

    if (contentSaveButton) {
        contentSaveButton.addEventListener('click', function () {
            saveSelectedPresetContent();
        });
    }

    if (currentDeviceNode && page.device_label) {
        currentDeviceNode.textContent = String(page.device_label);
    }

    if (page.uri) {
        setCurrentUri(String(page.uri));
    }

    setSelectedTitle('Еще не выбран');
    setSelectedSelector('Еще не выбран');
    setEditMode('Desktop · Обычный');
    setTargetSource('Ждем выбор элемента');
    updateInsertContext();
    updateContentInspector();

    if (frame) {
        setFrameState('loading');
        setOverlay('Live-страница загружается', 'После загрузки кликните по нужному элементу прямо внутри страницы. Панель редактирования появится внутри iframe.');
        setStatus('Live-страница загружается. После загрузки кликайте по элементу прямо внутри iframe.', 'muted');

        frame.addEventListener('load', function () {
            setFrameState('ready');
            setOverlay('Страница готова к выбору', 'Можно кликать по элементам внутри iframe и сразу править оформление через floating panel.');
            setStatus('Live-страница готова. Кликните по элементу внутри iframe и меняйте дизайн во floating panel.', 'ok');
        });

        frame.addEventListener('error', function () {
            setFrameState('error');
            setOverlay('Не удалось загрузить live-страницу', 'Проверьте текущий URI, доступность frontend-страницы и picker token.');
            setStatus('Live-страница сейчас не загрузилась. Проверьте URI и доступность frontend-страницы.', 'warn');
        });
    }

    window.addEventListener('message', function (event) {
        var data = event ? event.data : null;
        var selector = '';
        var title = '';

        if (!frame || event.source !== frame.contentWindow || !data) {
            return;
        }

        if (data.type === 'nordicstyl-picker-status') {
            selectedTargetKey = String(data.target_key || '').trim();
            selectedTargetTitle = String(data.title || '').trim();
            setSelectedTitle(selectedTargetTitle || 'Еще не выбран');
            setEditMode(String(data.mode_label || '').trim() || 'Desktop · Обычный');
            setTargetSource(String(data.source_label || '').trim() || 'Ждем выбор элемента');

            if (data.storage_path) {
                setSelectedSelector(String(data.storage_path || '').trim());
            }

            updateInsertContext();
            updateContentInspector();
            setStatus(String(data.message || '').trim() || 'Live workspace получил обновление из iframe.', String(data.tone || 'muted'));
            return;
        }

        if (data.type === 'nordicstyl-picker-context') {
            selectedTargetKey = String(data.target_key || '').trim();
            selectedTargetTitle = String(data.title || '').trim();
            setSelectedTitle(selectedTargetTitle || 'Еще не выбран');
            setEditMode(String(data.mode_label || '').trim() || 'Desktop · Обычный');
            setTargetSource(String(data.source_label || '').trim() || 'Ждем выбор элемента');

            if (data.storage_path) {
                setSelectedSelector(String(data.storage_path || '').trim());
            }

            updateInsertContext();
            updateContentInspector();
            return;
        }

        if (data.type !== 'nordicstyl-picker') {
            return;
        }

        selector = String(data.storage_path || '').trim();
        title = String(data.title || '').trim();
        selectedTargetKey = String(data.target_key || '').trim();
        selectedTargetTitle = title;

        if (!selector) {
            return;
        }

        setSelectedTitle(title || 'Элемент выбран');
        setEditMode(String(data.mode_label || '').trim() || 'Desktop · Обычный');
        setTargetSource(String(data.source_label || '').trim() || 'Ждем выбор элемента');
        setSelectedSelector(selector);
        updateInsertContext();
        updateContentInspector();
        setStatus('Элемент выбран. Теперь меняйте стиль во floating panel внутри iframe и сохраняйте результат на сайт.', 'ok');
    });
})();