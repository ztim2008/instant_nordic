(function () {
    var bootstrap = window.NordicblocksDesignBlockBootstrap || {};
    var root = document.getElementById('nbd-editor');

    if (!root) {
        return;
    }

    var BREAKPOINTS = {
        desktop: { label: 'Компьютер', frameClass: 'nbde-canvas-frame--desktop' },
        tablet: { label: 'Планшет', frameClass: 'nbde-canvas-frame--tablet' },
        mobile: { label: 'Мобильный', frameClass: 'nbde-canvas-frame--mobile' }
    };

    var TYPE_LABELS = {
        text: 'Текст',
        image: 'Изображение',
        button: 'Кнопка',
        shape: 'Фигура',
        icon: 'Иконка',
        container: 'Контейнер',
        video: 'Видео',
        divider: 'Разделитель',
        svg: 'SVG'
    };

    var state = {
        editor: {
            stateUrl: bootstrap.stateUrl || root.dataset.stateUrl || '',
            saveUrl: bootstrap.saveUrl || root.dataset.saveUrl || '',
            canvasUrl: bootstrap.canvasUrl || root.dataset.canvasUrl || '',
            backUrl: bootstrap.backUrl || root.dataset.backUrl || '',
            placeUrl: bootstrap.placeUrl || root.dataset.placeUrl || '',
            csrfToken: bootstrap.csrfToken || ''
        },
        palette: [],
        pickers: {},
        documentState: {
            block: null,
            contract: null,
            lastSavedAt: null,
            version: 1
        },
        uiState: {
            activeBreakpoint: 'desktop',
            selectedElementId: null,
            isDirty: false,
            isSaving: false,
            canvasHeight: 820,
            canvasReady: false,
            lastError: ''
        }
    };

    var nodes = {
        titleInput: document.getElementById('nbd-title-input'),
        statusText: document.getElementById('nbd-status-text'),
        canvasMeta: document.getElementById('nbd-canvas-meta'),
        layersSummary: document.getElementById('nbd-layers-summary'),
        propertiesSummary: document.getElementById('nbd-properties-summary'),
        previewFrame: document.getElementById('nbd-preview-frame'),
        frameWrap: document.getElementById('nbd-canvas-frame-wrap'),
        blockCard: document.getElementById('nbd-block-card'),
        stageCard: document.getElementById('nbd-stage-card'),
        sectionCard: document.getElementById('nbd-section-card'),
        layersCard: document.getElementById('nbd-layers-card'),
        propertiesCard: document.getElementById('nbd-properties-card'),
        saveButton: document.getElementById('nbd-save-button')
    };

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function getBreakpointLabel(key) {
        return (BREAKPOINTS[key] || BREAKPOINTS.desktop).label;
    }

    function getTypeLabel(type) {
        var fromPalette = state.palette.find(function (item) {
            return item.type === type;
        });

        if (fromPalette && fromPalette.label) {
            return fromPalette.label;
        }

        return TYPE_LABELS[type] || type || 'Элемент';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getPath(target, path, fallback) {
        var segments = String(path || '').split('.');
        var cursor = target;

        for (var i = 0; i < segments.length; i++) {
            if (!segments[i]) {
                continue;
            }
            if (!cursor || typeof cursor !== 'object' || !(segments[i] in cursor)) {
                return fallback;
            }
            cursor = cursor[segments[i]];
        }

        return cursor;
    }

    function setPath(target, path, value) {
        var segments = String(path || '').split('.');
        var cursor = target;

        for (var i = 0; i < segments.length; i++) {
            var key = segments[i];
            if (!key) {
                continue;
            }
            if (i === segments.length - 1) {
                cursor[key] = value;
                return;
            }
            if (!cursor[key] || typeof cursor[key] !== 'object') {
                cursor[key] = {};
            }
            cursor = cursor[key];
        }
    }

    function coerceValue(input) {
        var kind = input.dataset.kind || 'string';

        if (kind === 'boolean') {
            return !!input.checked;
        }

        if (kind === 'number') {
            if (input.value === '') {
                return 0;
            }
            var numeric = Number(input.value);
            return Number.isFinite(numeric) ? numeric : 0;
        }

        return input.value;
    }

    function defaultBranch(type) {
        var branch = {
            box: { x: 0, y: 0, w: 320, h: type === 'text' ? 96 : 96, zIndex: 1, visible: true },
            props: {
                opacityPct: 100,
                rotate: 0,
                backgroundColor: '',
                backgroundCss: '',
                borderRadius: 0,
                borderWidth: 0,
                borderColor: '',
                borderStyle: 'solid',
                boxShadow: '',
                blur: 0,
                backdropBlur: 0
            }
        };

        if (type === 'text') {
            branch.box.h = 140;
            branch.props.text = 'Новый текст';
            branch.props.tag = 'div';
            branch.props.color = '#0f172a';
            branch.props.fontSize = 36;
            branch.props.fontWeight = 800;
            branch.props.lineHeight = 120;
            branch.props.letterSpacing = 0;
            branch.props.textAlign = 'left';
            branch.props.textTransform = 'none';
        } else if (type === 'button') {
            branch.box.w = 220;
            branch.box.h = 56;
            branch.props.text = 'Нажмите сюда';
            branch.props.url = '#';
            branch.props.targetBlank = false;
            branch.props.color = '#ffffff';
            branch.props.fontSize = 16;
            branch.props.fontWeight = 700;
            branch.props.justifyContent = 'center';
            branch.props.backgroundColor = '#0f172a';
            branch.props.borderRadius = 999;
        } else if (type === 'image' || type === 'svg') {
            branch.box.w = 420;
            branch.box.h = 260;
            branch.props.src = '';
            branch.props.alt = '';
            branch.props.objectFit = 'cover';
            branch.props.backgroundColor = '#e2e8f0';
            branch.props.borderRadius = 24;
        } else if (type === 'video') {
            branch.box.w = 420;
            branch.box.h = 260;
            branch.props.src = '';
            branch.props.poster = '';
            branch.props.autoplay = false;
            branch.props.muted = true;
            branch.props.controls = true;
            branch.props.loop = false;
            branch.props.objectFit = 'cover';
            branch.props.borderRadius = 24;
        } else if (type === 'shape') {
            branch.box.w = 220;
            branch.box.h = 220;
            branch.props.shape = 'rect';
            branch.props.fill = '#f97316';
            branch.props.backgroundColor = '#f97316';
            branch.props.borderRadius = 24;
        } else if (type === 'icon') {
            branch.box.w = 72;
            branch.box.h = 72;
            branch.props.iconClass = 'fas fa-star';
            branch.props.color = '#0f172a';
            branch.props.size = 32;
        } else if (type === 'divider') {
            branch.box.w = 240;
            branch.box.h = 2;
            branch.props.color = '#cbd5e1';
            branch.props.orientation = 'horizontal';
            branch.props.backgroundColor = '#cbd5e1';
        } else if (type === 'container') {
            branch.box.w = 480;
            branch.box.h = 260;
            branch.props.layoutMode = 'flex';
            branch.props.direction = 'column';
            branch.props.justifyContent = 'flex-start';
            branch.props.alignItems = 'stretch';
            branch.props.gap = 16;
            branch.props.paddingTop = 20;
            branch.props.paddingRight = 20;
            branch.props.paddingBottom = 20;
            branch.props.paddingLeft = 20;
            branch.props.backgroundColor = 'rgba(255,255,255,0.42)';
            branch.props.borderRadius = 28;
        }

        return branch;
    }

    function normalizeElement(element, index) {
        var type = element && element.type ? String(element.type) : 'text';
        var normalized = clone(element || {});

        normalized.id = normalized.id || (type + '-' + (index + 1));
        normalized.type = type;
        normalized.name = normalized.name || ('Элемент ' + (index + 1));
        normalized.role = normalized.role || '';
        normalized.parentId = normalized.parentId || '';
        normalized.desktop = Object.assign(defaultBranch(type), normalized.desktop || {});
        normalized.desktop.box = Object.assign(defaultBranch(type).box, normalized.desktop.box || {});
        normalized.desktop.props = Object.assign(defaultBranch(type).props, normalized.desktop.props || {});
        normalized.tablet = normalized.tablet || clone(normalized.desktop);
        normalized.mobile = normalized.mobile || clone(normalized.tablet);
        normalized.tablet.box = Object.assign(clone(normalized.desktop.box), normalized.tablet.box || {});
        normalized.mobile.box = Object.assign(clone(normalized.tablet.box), normalized.mobile.box || {});
        normalized.tablet.props = Object.assign(clone(normalized.desktop.props), normalized.tablet.props || {});
        normalized.mobile.props = Object.assign(clone(normalized.tablet.props), normalized.mobile.props || {});

        return normalized;
    }

    function normalizeContract(contract) {
        var normalized = clone(contract || {});
        normalized.meta = normalized.meta || { contractVersion: 1, blockType: 'design_block', schemaVersion: 1, label: 'Дизайн-блок', status: 'active' };
        normalized.content = normalized.content || {};
        normalized.content.section = normalized.content.section || {};
        normalized.content.section.name = normalized.content.section.name || (state.documentState.block ? state.documentState.block.title : 'Дизайн-блок');
        normalized.content.section.tag = normalized.content.section.tag || 'section';
        normalized.content.section.elements = Array.isArray(normalized.content.section.elements) ? normalized.content.section.elements : [];
        normalized.content.section.elements = normalized.content.section.elements.map(normalizeElement);
        normalized.design = normalized.design || {};
        normalized.design.section = normalized.design.section || {};
        normalized.design.section.theme = normalized.design.section.theme || 'custom';
        normalized.design.section.background = Object.assign({
            mode: 'solid',
            color: '#f5f7fb',
            gradientFrom: '#f8fafc',
            gradientTo: '#e2e8f0',
            gradientAngle: 135,
            image: '',
            imagePosition: 'center center',
            imageSize: 'cover',
            imageRepeat: 'no-repeat',
            overlayColor: 'rgba(15,23,42,.18)',
            overlayOpacity: 18
        }, normalized.design.section.background || {});
        normalized.layout = normalized.layout || {};
        normalized.layout.stage = normalized.layout.stage || {};
        normalized.layout.stage.desktop = Object.assign({ width: 1200, minHeight: 640, paddingX: 24, paddingY: 24 }, normalized.layout.stage.desktop || {});
        normalized.layout.stage.tablet = Object.assign(clone(normalized.layout.stage.desktop), { width: 768, minHeight: 540, paddingX: 20, paddingY: 20 }, normalized.layout.stage.tablet || {});
        normalized.layout.stage.mobile = Object.assign(clone(normalized.layout.stage.tablet), { width: 390, minHeight: 420, paddingX: 16, paddingY: 16 }, normalized.layout.stage.mobile || {});

        return normalized;
    }

    function currentBreakpoint() {
        return state.uiState.activeBreakpoint in BREAKPOINTS ? state.uiState.activeBreakpoint : 'desktop';
    }

    function getElements() {
        return state.documentState.contract && state.documentState.contract.content && state.documentState.contract.content.section
            ? state.documentState.contract.content.section.elements || []
            : [];
    }

    function getElementById(id) {
        var elements = getElements();
        for (var i = 0; i < elements.length; i++) {
            if (String(elements[i].id) === String(id)) {
                return elements[i];
            }
        }
        return null;
    }

    function ensureElementBranch(element, breakpoint) {
        if (!element[breakpoint]) {
            element[breakpoint] = clone(element.desktop || defaultBranch(element.type || 'text'));
        }
        element[breakpoint].box = Object.assign(clone((element.desktop || defaultBranch(element.type || 'text')).box), element[breakpoint].box || {});
        element[breakpoint].props = Object.assign(clone((element.desktop || defaultBranch(element.type || 'text')).props), element[breakpoint].props || {});
        return element[breakpoint];
    }

    function getSelectedElement() {
        return getElementById(state.uiState.selectedElementId);
    }

    function getSelectedBranch() {
        var selected = getSelectedElement();
        return selected ? ensureElementBranch(selected, currentBreakpoint()) : null;
    }

    function updateStatus(message, mode) {
        nodes.statusText.textContent = message;
        nodes.statusText.classList.toggle('is-dirty', mode === 'dirty');
        nodes.statusText.classList.toggle('is-error', mode === 'error');
    }

    function setDirty(message) {
        state.uiState.isDirty = true;
        updateStatus(message || 'Изменения не сохранены', 'dirty');
        renderTopbar();
    }

    function setSaved(message) {
        state.uiState.isDirty = false;
        updateStatus(message || 'Сохранено', 'saved');
        renderTopbar();
    }

    function selectElement(id, syncCanvas) {
        var element = getElementById(id);

        state.uiState.selectedElementId = element ? element.id : null;
        renderLayersCard();
        renderPropertiesCard();
        renderSummary();

        if (syncCanvas !== false) {
            syncCanvasSelection();
        }
    }

    function renderTopbar() {
        var buttons = root.querySelectorAll('[data-breakpoint]');
        var active = currentBreakpoint();

        buttons.forEach(function (button) {
            button.classList.toggle('is-active', button.getAttribute('data-breakpoint') === active);
        });

        if (state.documentState.block) {
            nodes.titleInput.value = state.documentState.block.title || '';
        }

        nodes.saveButton.disabled = state.uiState.isSaving;
        nodes.saveButton.textContent = state.uiState.isSaving ? 'Сохраняем...' : 'Сохранить';
    }

    function renderSummary() {
        var elements = getElements();
        var selected = getSelectedElement();
        var stage = getPath(state.documentState.contract, 'layout.stage.' + currentBreakpoint(), {});

        nodes.canvasMeta.textContent = 'Элементы: ' + elements.length + ' · Режим: ' + getBreakpointLabel(currentBreakpoint()) + ' · ' + (stage.width || '-') + 'x' + (stage.minHeight || '-');
        nodes.layersSummary.textContent = elements.length + ' элементов';
        nodes.propertiesSummary.textContent = selected ? (selected.name || selected.id) + ' · ' + getTypeLabel(selected.type) : 'Ничего не выбрано';
        applyFrameMode();
    }

    function applyFrameMode() {
        var active = currentBreakpoint();
        nodes.frameWrap.className = 'nbde-canvas-frame ' + BREAKPOINTS[active].frameClass;
        nodes.previewFrame.style.minHeight = Math.max(560, Number(state.uiState.canvasHeight || 820)) + 'px';
    }

    function metric(label, value) {
        return '<div class="nbde-metric"><span>' + escapeHtml(label) + '</span><strong>' + escapeHtml(value) + '</strong></div>';
    }

    function field(label, path, value, scope, kind, extra) {
        extra = extra || '';
        kind = kind || 'string';
        return '<div class="nbde-field">'
            + '<label>' + escapeHtml(label) + '</label>'
            + '<input type="' + (kind === 'number' ? 'number' : 'text') + '" value="' + escapeHtml(value) + '" data-bind-scope="' + escapeHtml(scope) + '" data-bind-path="' + escapeHtml(path) + '" data-kind="' + escapeHtml(kind) + '" ' + extra + '>'
            + '</div>';
    }

    function selectField(label, path, value, scope, options) {
        return '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><select data-bind-scope="' + escapeHtml(scope) + '" data-bind-path="' + escapeHtml(path) + '">' + options.map(function (option) {
            return '<option value="' + escapeHtml(option.value) + '"' + (String(option.value) === String(value) ? ' selected' : '') + '>' + escapeHtml(option.label) + '</option>';
        }).join('') + '</select></div>';
    }

    function textareaField(label, path, value, scope) {
        return '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><textarea data-bind-scope="' + escapeHtml(scope) + '" data-bind-path="' + escapeHtml(path) + '">' + escapeHtml(value) + '</textarea></div>';
    }

    function checkboxField(label, path, value, scope) {
        return '<label class="nbde-inline-boolean"><input type="checkbox"' + (value ? ' checked' : '') + ' data-bind-scope="' + escapeHtml(scope) + '" data-bind-path="' + escapeHtml(path) + '" data-kind="boolean">' + escapeHtml(label) + '</label>';
    }

    function colorField(label, path, value, scope) {
        var safe = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(String(value || '')) ? String(value) : '#0f172a';
        return '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><div class="nbde-color-pair"><input type="color" value="' + escapeHtml(safe) + '" data-bind-scope="' + escapeHtml(scope) + '" data-bind-path="' + escapeHtml(path) + '"><input type="text" value="' + escapeHtml(value || '') + '" data-bind-scope="' + escapeHtml(scope) + '" data-bind-path="' + escapeHtml(path) + '"></div></div>';
    }

    function renderBlockCard() {
        var block = state.documentState.block || { id: bootstrap.blockId || 0, title: '' };
        var stage = getPath(state.documentState.contract, 'layout.stage.' + currentBreakpoint(), {});
        var paletteButtons = state.palette.map(function (item) {
            return '<button type="button" class="nbde-palette-button" data-add-type="' + escapeHtml(item.type) + '"><span class="nbde-layer-type">' + escapeHtml(getTypeLabel(item.type)) + '</span><div><strong>' + escapeHtml(item.label) + '</strong><span>' + escapeHtml(item.description || '') + '</span></div></button>';
        }).join('');

        nodes.blockCard.innerHTML = '<div class="nbde-metric-grid">'
            + metric('ID блока', block.id)
            + metric('Элементы', getElements().length)
            + metric('Холст', (stage.width || '-') + 'x' + (stage.minHeight || '-'))
            + '</div>'
            + '<p class="nbde-subtitle">Палитра добавляет элементы прямо в контракт. Холст слева закреплён, а все тонкие настройки собраны справа в компактной панели.</p>'
            + '<div class="nbde-palette-grid">' + paletteButtons + '</div>'
            + '<div class="nbde-action-grid">'
            + '<button type="button" class="nbde-mini-button" data-action="reload-state">Перечитать состояние</button>'
            + '<button type="button" class="nbde-mini-button" data-action="reload-preview">Обновить холст</button>'
            + '<button type="button" class="nbde-danger-button" data-action="delete-selected">Удалить выбранный</button>'
            + '<a class="nbde-mini-button" href="' + escapeHtml(state.editor.placeUrl || root.dataset.placeUrl || '') + '">Разместить в виджетах</a>'
            + '</div>';
    }

    function renderStageCard() {
        var branch = getPath(state.documentState.contract, 'layout.stage.' + currentBreakpoint(), {});

        nodes.stageCard.innerHTML = '<div class="nbde-field-grid nbde-field-grid--2">'
            + field('Ширина холста', 'width', branch.width || 1200, 'stage', 'number', 'min="240" max="1920"')
            + field('Минимальная высота', 'minHeight', branch.minHeight || 640, 'stage', 'number', 'min="160" max="1800"')
            + field('Внутренний отступ X', 'paddingX', branch.paddingX || 24, 'stage', 'number', 'min="0" max="200"')
            + field('Внутренний отступ Y', 'paddingY', branch.paddingY || 24, 'stage', 'number', 'min="0" max="200"')
            + '</div>'
            + '<p class="nbde-subtitle">Эти параметры относятся только к текущему режиму. После сохранения SSR-холст использует их без отдельной фронтенд-магии.</p>';
    }

    function renderSectionCard() {
        var section = getPath(state.documentState.contract, 'content.section', {});
        var background = getPath(state.documentState.contract, 'design.section.background', {});

        nodes.sectionCard.innerHTML = '<div class="nbde-field-grid nbde-field-grid--2">'
            + field('Название секции', 'name', section.name || '', 'sectionContent')
            + selectField('HTML тег', 'tag', section.tag || 'section', 'sectionContent', [
                { value: 'section', label: 'section' },
                { value: 'div', label: 'div' }
            ])
            + selectField('Режим фона', 'mode', background.mode || 'solid', 'background', [
                { value: 'solid', label: 'Сплошной цвет' },
                { value: 'gradient', label: 'Градиент' },
                { value: 'image', label: 'Изображение' },
                { value: 'theme', label: 'Тема блока' }
            ])
            + field('Угол градиента', 'gradientAngle', background.gradientAngle || 135, 'background', 'number', 'min="0" max="360"')
            + '</div>'
            + colorField('Основной цвет', 'color', background.color || '#f5f7fb', 'background')
            + '<div class="nbde-field-grid nbde-field-grid--2">'
            + colorField('Градиент от', 'gradientFrom', background.gradientFrom || '#f8fafc', 'background')
            + colorField('Градиент до', 'gradientTo', background.gradientTo || '#e2e8f0', 'background')
            + '</div>'
            + field('Ссылка на фоновое изображение', 'image', background.image || '', 'background')
            + '<div class="nbde-field-grid nbde-field-grid--2">'
            + field('Позиция изображения', 'imagePosition', background.imagePosition || 'center center', 'background')
            + field('Размер изображения', 'imageSize', background.imageSize || 'cover', 'background')
            + field('Повтор изображения', 'imageRepeat', background.imageRepeat || 'no-repeat', 'background')
            + colorField('Цвет затемнения', 'overlayColor', background.overlayColor || 'rgba(15,23,42,.18)', 'background')
            + field('Прозрачность затемнения', 'overlayOpacity', background.overlayOpacity || 18, 'background', 'number', 'min="0" max="100"')
            + '</div>';
    }

    function layerDepth(element) {
        var depth = 0;
        var parentId = element && element.parentId ? String(element.parentId) : '';
        while (parentId) {
            depth += 1;
            var parent = getElementById(parentId);
            if (!parent || !parent.parentId || depth > 20) {
                break;
            }
            parentId = String(parent.parentId);
        }
        return depth;
    }

    function renderLayersCard() {
        var elements = getElements().slice().sort(function (a, b) {
            var az = getPath(a, 'desktop.box.zIndex', 0);
            var bz = getPath(b, 'desktop.box.zIndex', 0);
            return bz - az;
        });

        if (!elements.length) {
            nodes.layersCard.innerHTML = '<div class="nbde-empty-state">В блоке пока нет элементов. Добавьте их из палитры выше.</div>';
            return;
        }

        nodes.layersCard.innerHTML = '<div class="nbde-layer-list">' + elements.map(function (element) {
            var selected = String(element.id) === String(state.uiState.selectedElementId);
            var depth = layerDepth(element);
            var branch = ensureElementBranch(element, currentBreakpoint());
            return '<button type="button" class="nbde-layer-button' + (selected ? ' is-selected' : '') + '" data-select-element="' + escapeHtml(element.id) + '" style="padding-left:' + (14 + depth * 18) + 'px">'
                + '<div class="nbde-layer-meta"><span class="nbde-layer-type">' + escapeHtml(getTypeLabel(element.type)) + '</span><div><strong>' + escapeHtml(element.name || element.id) + '</strong><span>' + escapeHtml(element.id) + ' · слой ' + (branch.box.zIndex || 0) + '</span></div></div>'
                + '</button>';
        }).join('') + '</div>';
    }

    function renderElementSpecificFields(element, branch) {
        var props = branch.props || {};
        var type = element.type || 'text';

        if (type === 'text') {
            return textareaField('Текст', 'props.text', props.text || '', 'element')
                + '<div class="nbde-field-grid nbde-field-grid--2">'
                + selectField('Тег', 'props.tag', props.tag || 'div', 'element', [
                    { value: 'div', label: 'div' },
                    { value: 'p', label: 'p' },
                    { value: 'span', label: 'span' },
                    { value: 'h1', label: 'h1' },
                    { value: 'h2', label: 'h2' },
                    { value: 'h3', label: 'h3' },
                    { value: 'h4', label: 'h4' }
                ])
                + colorField('Цвет текста', 'props.color', props.color || '#0f172a', 'element')
                + field('Размер шрифта', 'props.fontSize', props.fontSize || 36, 'element', 'number')
                + field('Вес', 'props.fontWeight', props.fontWeight || 800, 'element', 'number')
                + field('Межстрочный интервал %', 'props.lineHeight', props.lineHeight || 120, 'element', 'number')
                + field('Межбуквенный интервал', 'props.letterSpacing', props.letterSpacing || 0, 'element', 'number')
                + selectField('Выравнивание', 'props.textAlign', props.textAlign || 'left', 'element', [
                    { value: 'left', label: 'Слева' },
                    { value: 'center', label: 'По центру' },
                    { value: 'right', label: 'Справа' }
                ])
                + selectField('Регистр', 'props.textTransform', props.textTransform || 'none', 'element', [
                    { value: 'none', label: 'Без изменений' },
                    { value: 'uppercase', label: 'Верхний' },
                    { value: 'lowercase', label: 'Нижний' }
                ])
                + '</div>';
        }

        if (type === 'button') {
            return '<div class="nbde-field-grid nbde-field-grid--2">'
                + field('Текст кнопки', 'props.text', props.text || 'Подробнее', 'element')
                + field('URL', 'props.url', props.url || '#', 'element')
                + colorField('Цвет текста', 'props.color', props.color || '#ffffff', 'element')
                + colorField('Цвет фона', 'props.backgroundColor', props.backgroundColor || '#0f172a', 'element')
                + field('Размер текста', 'props.fontSize', props.fontSize || 16, 'element', 'number')
                + field('Радиус', 'props.borderRadius', props.borderRadius || 999, 'element', 'number')
                + selectField('Выравнивание', 'props.justifyContent', props.justifyContent || 'center', 'element', [
                    { value: 'flex-start', label: 'Слева' },
                    { value: 'center', label: 'По центру' },
                    { value: 'flex-end', label: 'Справа' }
                ])
                + '</div>' + checkboxField('Открывать в новой вкладке', 'props.targetBlank', !!props.targetBlank, 'element');
        }

        if (type === 'image' || type === 'svg') {
            return field('Источник', 'props.src', props.src || '', 'element')
                + field('Alt-текст', 'props.alt', props.alt || '', 'element')
                + selectField('Режим вписывания', 'props.objectFit', props.objectFit || 'cover', 'element', [
                    { value: 'cover', label: 'Обрезать по рамке' },
                    { value: 'contain', label: 'Вписать целиком' },
                    { value: 'fill', label: 'Растянуть' }
                ]);
        }

        if (type === 'video') {
            return field('Видео URL', 'props.src', props.src || '', 'element')
                + field('Постер', 'props.poster', props.poster || '', 'element')
                + selectField('Режим вписывания', 'props.objectFit', props.objectFit || 'cover', 'element', [
                    { value: 'cover', label: 'Обрезать по рамке' },
                    { value: 'contain', label: 'Вписать целиком' },
                    { value: 'fill', label: 'Растянуть' }
                ])
                + '<div class="nbde-field-grid nbde-field-grid--2">'
                + checkboxField('Автозапуск', 'props.autoplay', !!props.autoplay, 'element')
                + checkboxField('Без звука', 'props.muted', props.muted !== false, 'element')
                + checkboxField('Показывать элементы управления', 'props.controls', props.controls !== false, 'element')
                + checkboxField('Зациклить', 'props.loop', !!props.loop, 'element')
                + '</div>';
        }

        if (type === 'shape') {
            return '<div class="nbde-field-grid nbde-field-grid--2">'
                + selectField('Форма', 'props.shape', props.shape || 'rect', 'element', [
                    { value: 'rect', label: 'Прямоугольник' },
                    { value: 'pill', label: 'Капсула' },
                    { value: 'circle', label: 'Круг' }
                ])
                + colorField('Заливка', 'props.fill', props.fill || '#f97316', 'element')
                + '</div>';
        }

        if (type === 'icon') {
            return '<div class="nbde-field-grid nbde-field-grid--2">'
                + field('Класс иконки', 'props.iconClass', props.iconClass || 'fas fa-star', 'element')
                + colorField('Цвет', 'props.color', props.color || '#0f172a', 'element')
                + field('Размер', 'props.size', props.size || 32, 'element', 'number')
                + '</div>';
        }

        if (type === 'divider') {
            return '<div class="nbde-field-grid nbde-field-grid--2">'
                + colorField('Цвет', 'props.color', props.color || '#cbd5e1', 'element')
                + selectField('Ориентация', 'props.orientation', props.orientation || 'horizontal', 'element', [
                    { value: 'horizontal', label: 'Горизонтально' },
                    { value: 'vertical', label: 'Вертикально' }
                ])
                + '</div>';
        }

        if (type === 'container') {
            return '<div class="nbde-field-grid nbde-field-grid--2">'
                + selectField('Режим контейнера', 'props.layoutMode', props.layoutMode || 'flex', 'element', [
                    { value: 'absolute', label: 'Свободное позиционирование' },
                    { value: 'flex', label: 'Flex-контейнер' }
                ])
                + selectField('Направление', 'props.direction', props.direction || 'column', 'element', [
                    { value: 'column', label: 'Колонка' },
                    { value: 'row', label: 'Ряд' }
                ])
                + selectField('Распределение', 'props.justifyContent', props.justifyContent || 'flex-start', 'element', [
                    { value: 'flex-start', label: 'К началу' },
                    { value: 'center', label: 'По центру' },
                    { value: 'flex-end', label: 'К концу' },
                    { value: 'space-between', label: 'Равномерно' }
                ])
                + selectField('Выравнивание элементов', 'props.alignItems', props.alignItems || 'stretch', 'element', [
                    { value: 'stretch', label: 'Растянуть' },
                    { value: 'flex-start', label: 'К началу' },
                    { value: 'center', label: 'По центру' },
                    { value: 'flex-end', label: 'К концу' }
                ])
                + field('Интервал', 'props.gap', props.gap || 16, 'element', 'number')
                + field('Отступ сверху', 'props.paddingTop', props.paddingTop || 0, 'element', 'number')
                + field('Отступ справа', 'props.paddingRight', props.paddingRight || 0, 'element', 'number')
                + field('Отступ снизу', 'props.paddingBottom', props.paddingBottom || 0, 'element', 'number')
                + field('Отступ слева', 'props.paddingLeft', props.paddingLeft || 0, 'element', 'number')
                + '</div>';
        }

        return '<div class="nbde-empty-state">Для этого типа пока нет специальных полей. Можно менять геометрию и базовый внешний вид.</div>';
    }

    function renderPropertiesCard() {
        var element = getSelectedElement();
        if (!element) {
            nodes.propertiesCard.innerHTML = '<div class="nbde-empty-state">Выберите элемент на холсте или в списке слоёв. После этого здесь появятся геометрия и настройки выбранного типа.</div>';
            return;
        }

        var branch = getSelectedBranch();
        var box = branch.box || {};
        var props = branch.props || {};
        var appearance = '<div class="nbde-card-divider"></div><div class="nbde-field-grid nbde-field-grid--2">'
            + colorField('Фон', 'props.backgroundColor', props.backgroundColor || '', 'element')
            + field('CSS фона', 'props.backgroundCss', props.backgroundCss || '', 'element')
            + field('Радиус', 'props.borderRadius', props.borderRadius || 0, 'element', 'number')
            + field('Толщина границы', 'props.borderWidth', props.borderWidth || 0, 'element', 'number')
            + colorField('Цвет границы', 'props.borderColor', props.borderColor || '', 'element')
            + selectField('Стиль границы', 'props.borderStyle', props.borderStyle || 'solid', 'element', [
                { value: 'solid', label: 'Сплошная' },
                { value: 'dashed', label: 'Пунктир' },
                { value: 'dotted', label: 'Точки' }
            ])
            + field('Тень', 'props.boxShadow', props.boxShadow || '', 'element')
            + field('Непрозрачность %', 'props.opacityPct', props.opacityPct || 100, 'element', 'number')
            + field('Поворот', 'props.rotate', props.rotate || 0, 'element', 'number')
            + field('Размытие', 'props.blur', props.blur || 0, 'element', 'number')
            + field('Размытие подложки', 'props.backdropBlur', props.backdropBlur || 0, 'element', 'number')
            + '</div>';

        nodes.propertiesCard.innerHTML = '<div class="nbde-field-grid nbde-field-grid--2">'
            + field('Имя', 'name', element.name || '', 'elementMeta')
            + field('Роль', 'role', element.role || '', 'elementMeta')
            + field('Родитель', 'parentId', element.parentId || '', 'elementMeta')
            + field('ID', 'id', element.id || '', 'elementMeta')
            + field('X', 'box.x', box.x || 0, 'element', 'number')
            + field('Y', 'box.y', box.y || 0, 'element', 'number')
            + field('Ширина', 'box.w', box.w || 320, 'element', 'number')
            + field('Высота', 'box.h', box.h || 120, 'element', 'number')
            + field('Слой Z', 'box.zIndex', box.zIndex || 1, 'element', 'number')
            + '</div>'
            + checkboxField('Показывать элемент', 'box.visible', box.visible !== false, 'element')
            + '<div class="nbde-card-divider"></div>'
            + renderElementSpecificFields(element, branch)
            + appearance;
    }

    function renderAll() {
        renderTopbar();
        renderSummary();
        renderBlockCard();
        renderStageCard();
        renderSectionCard();
        renderLayersCard();
        renderPropertiesCard();
        syncCanvasSelection();
    }

    function collectDescendantIds(id, acc) {
        acc = acc || [];
        getElements().forEach(function (element) {
            if (String(element.parentId || '') === String(id)) {
                acc.push(String(element.id));
                collectDescendantIds(element.id, acc);
            }
        });
        return acc;
    }

    function deleteSelected() {
        var selected = getSelectedElement();
        if (!selected) {
            updateStatus('Сначала выберите элемент для удаления', 'error');
            return;
        }

        var ids = [String(selected.id)].concat(collectDescendantIds(selected.id));
        state.documentState.contract.content.section.elements = getElements().filter(function (element) {
            return ids.indexOf(String(element.id)) === -1;
        });
        state.uiState.selectedElementId = getElements().length ? getElements()[0].id : null;
        setDirty('Элемент удалён. Не забудьте сохранить.');
        renderAll();
        reloadPreview();
    }

    function syncParentReferences(previousId, nextId) {
        if (!previousId || !nextId || previousId === nextId) {
            return;
        }

        getElements().forEach(function (element) {
            if (String(element.parentId || '') === String(previousId)) {
                element.parentId = nextId;
            }
        });
    }

    function ensureUniqueElementId(candidate, currentId) {
        var sanitized = String(candidate || '').trim().toLowerCase().replace(/[^a-z0-9_\-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');

        if (!sanitized) {
            return currentId;
        }

        var existing = {};
        getElements().forEach(function (element) {
            if (String(element.id) !== String(currentId)) {
                existing[String(element.id)] = true;
            }
        });

        var unique = sanitized;
        var index = 2;
        while (existing[unique]) {
            unique = sanitized + '-' + index;
            index += 1;
        }

        return unique;
    }

    function nextElementId(type) {
        var base = String(type || 'element').replace(/[^a-z0-9_\-]/gi, '-').toLowerCase();
        var existing = {};
        getElements().forEach(function (element) {
            existing[String(element.id)] = true;
        });

        var index = getElements().length + 1;
        var candidate = base + '-' + index;
        while (existing[candidate]) {
            index += 1;
            candidate = base + '-' + index;
        }

        return candidate;
    }

    function addElement(type) {
        var id = nextElementId(type);
        var offset = getElements().length * 26;
        var element = normalizeElement({
            id: id,
            type: type,
            name: (state.palette.find(function (item) { return item.type === type; }) || {}).label || id,
            desktop: {
                box: {
                    x: 48 + offset,
                    y: 56 + offset,
                    zIndex: getElements().length + 2
                }
            }
        }, getElements().length);

        state.documentState.contract.content.section.elements.push(element);
        state.uiState.selectedElementId = element.id;
        setDirty('Элемент добавлен. Настройте свойства справа и сохраните изменения.');
        renderAll();
        reloadPreview();
    }

    function syncCanvasSelection() {
        if (!nodes.previewFrame || !nodes.previewFrame.contentWindow) {
            return;
        }

        nodes.previewFrame.contentWindow.postMessage({
            source: 'nordicblocks-design-editor',
            type: 'select-element',
            elementId: state.uiState.selectedElementId || '',
            scrollIntoView: true
        }, '*');
    }

    function reloadPreview() {
        var url = (state.editor.canvasUrl || root.dataset.canvasUrl || '').replace(/([?&])_ts=\d+/, '$1').replace(/[?&]$/, '');
        if (!url) {
            return;
        }

        nodes.previewFrame.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_ts=' + Date.now();
    }

    async function saveContract() {
        if (!state.documentState.contract || !state.documentState.block) {
            return;
        }

        state.uiState.isSaving = true;
        renderTopbar();
        updateStatus('Сохраняем изменения...', 'saved');

        try {
            var response = await fetch((state.editor.saveUrl || root.dataset.saveUrl || '') + '?csrf_token=' + encodeURIComponent(state.editor.csrfToken || ''), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    title: state.documentState.block.title || '',
                    contract: state.documentState.contract,
                    csrf_token: state.editor.csrfToken || ''
                })
            });
            var result = await response.json();
            if (!result || !result.ok) {
                throw new Error(result && result.error ? result.error : 'save_failed');
            }

            state.documentState.contract = normalizeContract(result.contract || state.documentState.contract);
            state.documentState.lastSavedAt = Date.now();
            setSaved('Сохранено. Холст можно обновлять без потери выделения.');
            renderAll();
            reloadPreview();
        } catch (error) {
            updateStatus('Ошибка сохранения: ' + (error && error.message ? error.message : 'save_failed'), 'error');
        } finally {
            state.uiState.isSaving = false;
            renderTopbar();
        }
    }

    async function loadState() {
        updateStatus('Загружаем состояние дизайн-блока...', 'saved');

        var response = await fetch(state.editor.stateUrl || root.dataset.stateUrl || '', {
            credentials: 'same-origin'
        });
        var result = await response.json();

        if (!result || !result.ok) {
            throw new Error(result && result.error ? result.error : 'state_load_failed');
        }

        state.documentState.block = result.block || { id: bootstrap.blockId || 0, title: '' };
        state.documentState.contract = normalizeContract(result.contract || {});
        state.palette = result.palette && Array.isArray(result.palette.items) ? result.palette.items : [];
        state.pickers = result.pickers || {};
        state.editor = Object.assign(state.editor, result.editor || {});
        state.uiState.activeBreakpoint = result.ui && result.ui.activeBreakpoint ? result.ui.activeBreakpoint : state.uiState.activeBreakpoint;
        state.uiState.selectedElementId = result.ui && result.ui.selectedElementId ? result.ui.selectedElementId : (getElements()[0] ? getElements()[0].id : null);
        state.uiState.canvasReady = false;
        setSaved('Редактор готов. Кликните элемент на холсте или добавьте новый из палитры.');
        renderAll();
    }

    function handleBindingInput(input) {
        var scope = input.dataset.bindScope;
        var path = input.dataset.bindPath;
        var value = coerceValue(input);

        if (!scope || !path || !state.documentState.contract) {
            return;
        }

        if (scope === 'stage') {
            setPath(getPath(state.documentState.contract, 'layout.stage.' + currentBreakpoint(), {}), path, value);
        } else if (scope === 'sectionContent') {
            setPath(state.documentState.contract.content.section, path, value);
        } else if (scope === 'background') {
            setPath(state.documentState.contract.design.section.background, path, value);
        } else if (scope === 'element') {
            var branch = getSelectedBranch();
            if (!branch) {
                return;
            }
            setPath(branch, path, value);
        } else if (scope === 'elementMeta') {
            var element = getSelectedElement();
            if (!element) {
                return;
            }
            if (path === 'id') {
                var previousId = element.id;
                value = ensureUniqueElementId(value, element.id);
                syncParentReferences(previousId, value);
                state.uiState.selectedElementId = value;
            }
            setPath(element, path, value);
        }

        if (scope === 'elementMeta' || path.indexOf('box.') === 0 || path === 'name' || path === 'parentId') {
            renderLayersCard();
            renderPropertiesCard();
            renderSummary();
        }

        setDirty('Изменения готовы к сохранению');
    }

    root.addEventListener('input', function (event) {
        var target = event.target;

        if (target === nodes.titleInput) {
            if (state.documentState.block) {
                state.documentState.block.title = nodes.titleInput.value;
            }
            setDirty('Название изменено');
            return;
        }

        if (target.matches('[data-bind-scope]')) {
            handleBindingInput(target);
        }
    });

    root.addEventListener('change', function (event) {
        var target = event.target;
        if (target.matches('[data-bind-scope]')) {
            handleBindingInput(target);
            renderAll();
        }
    });

    root.addEventListener('click', function (event) {
        var target = event.target.closest('[data-breakpoint],[data-action],[data-add-type],[data-select-element]');
        if (!target) {
            return;
        }

        if (target.hasAttribute('data-breakpoint')) {
            state.uiState.activeBreakpoint = target.getAttribute('data-breakpoint');
            renderAll();
            return;
        }

        if (target.hasAttribute('data-add-type')) {
            addElement(target.getAttribute('data-add-type'));
            return;
        }

        if (target.hasAttribute('data-select-element')) {
            selectElement(target.getAttribute('data-select-element'));
            updateStatus('Выбран элемент: ' + target.getAttribute('data-select-element'), 'saved');
            return;
        }

        var action = target.getAttribute('data-action');
        if (action === 'reload-preview') {
            reloadPreview();
            return;
        }
        if (action === 'reload-state') {
            loadState().catch(function (error) {
                updateStatus('Ошибка перечитывания состояния: ' + error.message, 'error');
            });
            return;
        }
        if (action === 'delete-selected') {
            deleteSelected();
        }
    });

    nodes.saveButton.addEventListener('click', function () {
        saveContract();
    });

    nodes.previewFrame.addEventListener('load', function () {
        state.uiState.canvasReady = true;
        window.setTimeout(function () {
            syncCanvasSelection();
            if (nodes.previewFrame && nodes.previewFrame.contentWindow) {
                nodes.previewFrame.contentWindow.postMessage({
                    source: 'nordicblocks-design-editor',
                    type: 'request-metrics'
                }, '*');
            }
        }, 60);
    });

    window.addEventListener('message', function (event) {
        var data = event.data || {};
        if (data.source !== 'nordicblocks-design-canvas') {
            return;
        }

        if (data.type === 'canvas:metrics') {
            state.uiState.canvasHeight = Math.max(560, Number(data.height || 820));
            applyFrameMode();
            return;
        }

        if (data.type === 'select-element') {
            if (data.elementId) {
                selectElement(data.elementId, false);
                updateStatus('Выбран элемент: ' + data.elementId, 'saved');
            }
            return;
        }

        if (data.type === 'ready') {
            state.uiState.canvasReady = true;
            syncCanvasSelection();
        }
    });

    loadState().catch(function (error) {
        updateStatus('Ошибка загрузки редактора: ' + (error && error.message ? error.message : 'state_load_failed'), 'error');
    });
})();