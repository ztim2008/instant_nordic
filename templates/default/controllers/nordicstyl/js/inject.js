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
    var selectedTitle = '';
    var hoveredElement = null;
    var saveInFlight = false;
    var loadToken = 0;
    var deviceKey = 'base';
    var stateKey = 'default';
    var currentRule = createEmptyRule('');
    var fields = {};

    function createEmptyRule(selector) {
        return {
            id: 0,
            title: '',
            path: selector || '',
            styles: {},
            custom: {}
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

    function selectorFor(element) {
        var parts = [];

        if (!element || element.nodeType !== 1) {
            return '';
        }

        if (element.id) {
            return '#' + cssEscape(element.id);
        }

        while (element && element.nodeType === 1) {
            var tag = String(element.tagName || '').toLowerCase();
            var part = tag;
            var parent;
            var siblings;
            var index;
            var classes = [];

            if (!tag || tag === 'html') {
                break;
            }

            if (element.id) {
                parts.unshift('#' + cssEscape(element.id));
                break;
            }

            if (element.classList && element.classList.length) {
                Array.prototype.slice.call(element.classList).some(function (className) {
                    if (!className || className.indexOf('js-') === 0 || className.indexOf('is-') === 0) {
                        return false;
                    }

                    classes.push(cssEscape(className));

                    return classes.length >= 3;
                });

                if (classes.length) {
                    part += '.' + classes.join('.');
                }
            }

            parent = element.parentElement;
            if (parent) {
                siblings = Array.prototype.filter.call(parent.children, function (child) {
                    return child && child.tagName === element.tagName;
                });

                if (siblings.length > 1) {
                    index = siblings.indexOf(element) + 1;
                    if (index > 0) {
                        part += ':nth-of-type(' + index + ')';
                    }
                }
            }

            parts.unshift(part);
            element = parent;

            if (element && String(element.tagName || '').toLowerCase() === 'body') {
                parts.unshift('body');
                break;
            }
        }

        return parts.join(' > ');
    }

    function guessTitle(element) {
        var text;

        if (!element || element.nodeType !== 1) {
            return 'Элемент';
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

    function setStatus(text, tone) {
        var statusNode = panel ? panel.querySelector('[data-live-editor-status]') : null;

        if (!statusNode) {
            return;
        }

        statusNode.textContent = text || '';
        statusNode.setAttribute('data-tone', tone || 'muted');
    }

    function syncMeta() {
        var selectorNode = panel.querySelector('[data-live-editor-selector]');
        var titleNode = panel.querySelector('[data-live-editor-title]');
        var deviceNode = panel.querySelector('[data-live-editor-device-note]');

        selectorNode.textContent = selectedSelector || 'Пока не выбран';
        titleNode.textContent = selectedTitle || 'Кликните по элементу на странице';
        deviceNode.textContent = deviceKey === 'base'
            ? 'Сейчас меняется базовый стиль для всех устройств.'
            : 'Сейчас меняется ' + deviceKey + '-override для выбранного элемента.';
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

    function loadRuleForSelector(selector) {
        var requestToken = loadToken + 1;

        loadToken = requestToken;
        currentRule = createEmptyRule(selector);
        syncForm();
        applyPreview();

        if (!config.style_rule_url || !window.fetch || !config.csrf_token || !selector) {
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
                selector: selector
            })
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (loadToken !== requestToken || selector !== selectedSelector) {
                return;
            }

            if (response && response.rule) {
                currentRule = {
                    id: parseInt(response.rule.id || 0, 10) || 0,
                    title: String(response.rule.title || ''),
                    path: String(response.rule.path || selector),
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

        if (saveInFlight) {
            return;
        }

        if (!selectedSelector) {
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
        if (!element || element === panel || panel.contains(element)) {
            return;
        }

        selectedElement = element;
        selectedSelector = selectorFor(element);
        selectedTitle = guessTitle(element);

        positionOutline(element);
        currentRule = createEmptyRule(selectedSelector);
        syncMeta();
        loadRuleForSelector(selectedSelector);

        try {
            window.parent.postMessage({ type: 'nordicstyl-picker', selector: selectedSelector }, window.location.origin);
        } catch (error) {
            return;
        }
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
        var close = doc.createElement('button');
        var meta = doc.createElement('div');
        var nameNode = doc.createElement('div');
        var selectorNode = doc.createElement('div');
        var switchers = doc.createElement('div');
        var deviceSelect = doc.createElement('select');
        var stateSelect = doc.createElement('select');
        var note = doc.createElement('div');
        var fieldsGrid = doc.createElement('div');
        var actions = doc.createElement('div');
        var saveButton = doc.createElement('button');
        var rulesButton = doc.createElement('a');
        var status = doc.createElement('div');

        shell.className = 'ns-live-editor';
        shell.innerHTML = '';

        header.className = 'ns-live-editor__header';
        titleWrap.className = 'ns-live-editor__title-wrap';
        title.className = 'ns-live-editor__title';
        title.textContent = 'Live style';
        subtitle.className = 'ns-live-editor__subtitle';
        subtitle.textContent = 'Кликните по элементу на странице и меняйте стиль сразу мышкой.';
        close.className = 'ns-live-editor__icon';
        close.type = 'button';
        close.textContent = '×';
        close.addEventListener('click', function () {
            shell.classList.toggle('is-collapsed');
        });

        titleWrap.appendChild(title);
        titleWrap.appendChild(subtitle);
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
            { value: 'base', label: 'Base' },
            { value: 'mobile', label: 'Mobile' }
        ].forEach(function (item) {
            var option = doc.createElement('option');
            option.value = item.value;
            option.textContent = item.label;
            deviceSelect.appendChild(option);
        });
        [
            { value: 'default', label: 'Обычный' },
            { value: 'hover', label: 'Hover' }
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
        rulesButton.className = 'ns-live-editor__button ns-live-editor__button--ghost';
        rulesButton.href = config.rules_url || '#';
        rulesButton.target = '_blank';
        rulesButton.rel = 'noopener';
        rulesButton.textContent = 'Открыть правила';
        actions.appendChild(saveButton);
        actions.appendChild(rulesButton);

        status.className = 'ns-live-editor__status';
        status.setAttribute('data-live-editor-status', '1');
        status.textContent = 'Выберите элемент на странице.';

        shell.appendChild(header);
        shell.appendChild(meta);
        shell.appendChild(switchers);
        shell.appendChild(note);
        shell.appendChild(fieldsGrid);
        shell.appendChild(actions);
        shell.appendChild(status);

        body.appendChild(shell);

        return shell;
    }

    panel = buildPanel();
    ensureOverlay();
    ensurePreviewStyleNode();
    syncForm();

    doc.addEventListener('mousemove', function (event) {
        var target = event.target;

        if (!target || target.nodeType !== 1 || target === panel || panel.contains(target)) {
            return;
        }

        hoveredElement = target;

        if (!selectedElement) {
            positionOutline(target);
        }
    }, true);

    doc.addEventListener('scroll', function () {
        positionOutline(selectedElement || hoveredElement);
    }, true);

    window.addEventListener('resize', function () {
        positionOutline(selectedElement || hoveredElement);
    });

    doc.addEventListener('click', function (event) {
        var target = event.target;

        if (!target || target.nodeType !== 1) {
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