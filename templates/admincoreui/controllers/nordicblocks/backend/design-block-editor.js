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
            lastError: ''
        },
        interactionState: {
            guideX: null,
            guideY: null,
            drag: null
        }
    };

    var nodes = {
        titleInput: document.getElementById('nbd-title-input'),
        statusText: document.getElementById('nbd-status-text'),
        canvasMeta: document.getElementById('nbd-canvas-meta'),
        layersSummary: document.getElementById('nbd-layers-summary'),
        propertiesSummary: document.getElementById('nbd-properties-summary'),
        frameWrap: document.getElementById('nbd-canvas-frame-wrap'),
        canvasStage: document.getElementById('nbd-canvas-stage'),
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

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function textToHtml(value) {
        return escapeHtml(value).replace(/\n/g, '<br>');
    }

    function getPath(target, path, fallback) {
        var segments = String(path || '').split('.');
        var cursor = target;
        var index;

        for (index = 0; index < segments.length; index++) {
            if (!segments[index]) {
                continue;
            }

            if (!cursor || typeof cursor !== 'object' || !(segments[index] in cursor)) {
                return fallback;
            }

            cursor = cursor[segments[index]];
        }

        return cursor;
    }

    function setPath(target, path, value) {
        var segments = String(path || '').split('.');
        var cursor = target;
        var index;

        for (index = 0; index < segments.length; index++) {
            var key = segments[index];

            if (!key) {
                continue;
            }

            if (index === segments.length - 1) {
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

    function currentBreakpoint() {
        return BREAKPOINTS[state.uiState.activeBreakpoint] ? state.uiState.activeBreakpoint : 'desktop';
    }

    function getBreakpointLabel(key) {
        return (BREAKPOINTS[key] || BREAKPOINTS.desktop).label;
    }

    function getTypeLabel(type) {
        var index;

        for (index = 0; index < state.palette.length; index++) {
            if (state.palette[index].type === type) {
                return state.palette[index].label || TYPE_LABELS[type] || type || 'Элемент';
            }
        }

        return TYPE_LABELS[type] || type || 'Элемент';
    }

    function defaultBranch(type) {
        var branch = {
            box: {
                x: 0,
                y: 0,
                w: 320,
                h: type === 'text' ? 120 : 96,
                zIndex: 1,
                visible: true
            },
            props: {
                opacityPct: 100,
                rotate: 0,
                backgroundColor: '',
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
            branch.props.text = 'Новый текст';
            branch.props.color = '#0f172a';
            branch.props.fontSize = 36;
            branch.props.fontWeight = 800;
            branch.props.lineHeight = 120;
            branch.props.letterSpacing = 0;
            branch.props.textAlign = 'left';
        } else if (type === 'button') {
            branch.box.w = 220;
            branch.box.h = 56;
            branch.props.text = 'Нажмите сюда';
            branch.props.url = '#';
            branch.props.color = '#ffffff';
            branch.props.fontSize = 16;
            branch.props.fontWeight = 700;
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
            branch.props.objectFit = 'cover';
            branch.props.borderRadius = 24;
        } else if (type === 'shape') {
            branch.box.w = 220;
            branch.box.h = 220;
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
            branch.props.backgroundColor = '#cbd5e1';
            branch.props.orientation = 'horizontal';
        } else if (type === 'container') {
            branch.box.w = 480;
            branch.box.h = 260;
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
        var base = defaultBranch(type);

        normalized.id = normalized.id || (type + '_' + (index + 1));
        normalized.type = type;
        normalized.name = normalized.name || (getTypeLabel(type) + ' ' + (index + 1));
        normalized.role = normalized.role || '';
        normalized.parentId = normalized.parentId || '';
        normalized.hidden = !!normalized.hidden;
        normalized.locked = !!normalized.locked;
        normalized.constraints = normalized.constraints || { horizontal: 'left', vertical: 'top' };
        normalized.desktop = Object.assign({}, base, normalized.desktop || {});
        normalized.desktop.box = Object.assign({}, base.box, (normalized.desktop || {}).box || {});
        normalized.desktop.props = Object.assign({}, base.props, (normalized.desktop || {}).props || {});
        normalized.tablet = normalized.tablet || clone(normalized.desktop);
        normalized.mobile = normalized.mobile || clone(normalized.tablet);
        normalized.tablet.box = Object.assign({}, normalized.desktop.box, normalized.tablet.box || {});
        normalized.tablet.props = Object.assign({}, normalized.desktop.props, normalized.tablet.props || {});
        normalized.mobile.box = Object.assign({}, normalized.tablet.box, normalized.mobile.box || {});
        normalized.mobile.props = Object.assign({}, normalized.tablet.props, normalized.mobile.props || {});

        return normalized;
    }

    function normalizeContract(contract) {
        var normalized = clone(contract || {});
        var blockTitle = state.documentState.block ? state.documentState.block.title : 'Дизайн-блок';

        normalized.meta = normalized.meta || {
            contractVersion: 3,
            blockType: 'design_block',
            schemaVersion: 1,
            label: 'Дизайн-блок',
            status: 'active'
        };
        normalized.content = normalized.content || {};
        normalized.content.section = normalized.content.section || {};
        normalized.content.section.name = normalized.content.section.name || blockTitle;
        normalized.content.section.tag = normalized.content.section.tag || 'section';
        normalized.content.section.elements = Array.isArray(normalized.content.section.elements) ? normalized.content.section.elements : [];
        normalized.content.section.elements = normalized.content.section.elements.map(function (element, elementIndex) {
            return normalizeElement(element, elementIndex);
        });

        normalized.design = normalized.design || {};
        normalized.design.section = normalized.design.section || {};
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
        normalized.layout.stage.desktop = Object.assign({ width: 1200, minHeight: 680, paddingX: 24, paddingY: 24 }, normalized.layout.stage.desktop || {});
        normalized.layout.stage.tablet = Object.assign({}, normalized.layout.stage.desktop, { width: 768, minHeight: 560, paddingX: 20, paddingY: 20 }, normalized.layout.stage.tablet || {});
        normalized.layout.stage.mobile = Object.assign({}, normalized.layout.stage.tablet, { width: 390, minHeight: 440, paddingX: 16, paddingY: 16 }, normalized.layout.stage.mobile || {});

        normalized.runtime = normalized.runtime || {};
        normalized.runtime.editor = Object.assign({
            snapToGrid: true,
            gridSize: 8,
            snapThreshold: 6,
            showGuides: true,
            showColumnsGrid: true,
            columnsCount: 12
        }, normalized.runtime.editor || {});

        return normalized;
    }

    function getElements() {
        if (!state.documentState.contract || !state.documentState.contract.content || !state.documentState.contract.content.section) {
            return [];
        }

        return state.documentState.contract.content.section.elements || [];
    }

    function getElementById(id) {
        var elements = getElements();
        var index;

        for (index = 0; index < elements.length; index++) {
            if (String(elements[index].id) === String(id)) {
                return elements[index];
            }
        }

        return null;
    }

    function getSelectedElement() {
        return getElementById(state.uiState.selectedElementId || '');
    }

    function currentStageConfig() {
        return getPath(state.documentState.contract, 'layout.stage.' + currentBreakpoint(), {});
    }

    function currentEditorRuntime() {
        return getPath(state.documentState.contract, 'runtime.editor', {});
    }

    function resolveBranch(element, breakpoint) {
        var branch = clone(element.desktop || defaultBranch(element.type));

        if (breakpoint === 'tablet' || breakpoint === 'mobile') {
            branch.box = Object.assign({}, branch.box, getPath(element, 'tablet.box', {}));
            branch.props = Object.assign({}, branch.props, getPath(element, 'tablet.props', {}));
        }

        if (breakpoint === 'mobile') {
            branch.box = Object.assign({}, branch.box, getPath(element, 'mobile.box', {}));
            branch.props = Object.assign({}, branch.props, getPath(element, 'mobile.props', {}));
        }

        return branch;
    }

    function currentEditableBranch(element) {
        var key = currentBreakpoint();

        if (!element[key]) {
            element[key] = clone(element.desktop || defaultBranch(element.type));
        }

        element[key].box = element[key].box || {};
        element[key].props = element[key].props || {};

        return element[key];
    }

    function buildTree(elements) {
        var indexed = {};
        var tree = [];
        var index;

        for (index = 0; index < elements.length; index++) {
            if (!elements[index] || !elements[index].id) {
                continue;
            }

            indexed[elements[index].id] = clone(elements[index]);
            indexed[elements[index].id].children = [];
        }

        Object.keys(indexed).forEach(function (id) {
            var element = indexed[id];

            if (element.parentId && indexed[element.parentId]) {
                indexed[element.parentId].children.push(element);
                return;
            }

            tree.push(element);
        });

        return tree;
    }

    function ensureSelectedElement() {
        var selected = getSelectedElement();
        var elements = getElements();

        if (selected) {
            return;
        }

        state.uiState.selectedElementId = elements.length ? elements[0].id : null;
    }

    function markDirty() {
        state.uiState.isDirty = true;
        renderStatus();
    }

    function clearGuides() {
        state.interactionState.guideX = null;
        state.interactionState.guideY = null;
    }

    function renderStatus() {
        if (!nodes.statusText) {
            return;
        }

        nodes.statusText.classList.remove('is-dirty');
        nodes.statusText.classList.remove('is-error');

        if (state.uiState.lastError) {
            nodes.statusText.textContent = state.uiState.lastError;
            nodes.statusText.classList.add('is-error');
            return;
        }

        if (state.uiState.isSaving) {
            nodes.statusText.textContent = 'Сохраняю изменения...';
            return;
        }

        if (state.uiState.isDirty) {
            nodes.statusText.textContent = 'Изменения есть только на холсте. Сохраните, чтобы зафиксировать контракт.';
            nodes.statusText.classList.add('is-dirty');
            return;
        }

        if (state.documentState.lastSavedAt) {
            nodes.statusText.textContent = 'Сохранено: ' + state.documentState.lastSavedAt;
            return;
        }

        nodes.statusText.textContent = 'Редактор готов. Перетаскивайте элементы и печатайте текст прямо на холсте.';
    }

    function updateCanvasMeta() {
        var stage = currentStageConfig();
        var elements = getElements();

        if (nodes.canvasMeta) {
            nodes.canvasMeta.textContent = getBreakpointLabel(currentBreakpoint()) + ' • ' + (stage.width || 0) + 'px • ' + (stage.minHeight || 0) + 'px • ' + elements.length + ' эл.';
        }
    }

    function renderTopbar() {
        if (nodes.titleInput && state.documentState.block) {
            nodes.titleInput.value = state.documentState.block.title || '';
        }

        Array.prototype.forEach.call(root.querySelectorAll('[data-breakpoint]'), function (button) {
            button.classList.toggle('is-active', button.dataset.breakpoint === currentBreakpoint());
        });

        if (nodes.frameWrap) {
            nodes.frameWrap.className = 'nbde-canvas-frame ' + (BREAKPOINTS[currentBreakpoint()] || BREAKPOINTS.desktop).frameClass;
        }
        renderStatus();
        updateCanvasMeta();
    }

    function renderField(label, scope, path, value, kind) {
        return '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><input type="' + (kind === 'number' ? 'number' : 'text') + '" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="' + escapeHtml(kind || 'string') + '" value="' + escapeHtml(value == null ? '' : value) + '"></div>';
    }

    function renderTextareaField(label, scope, path, value) {
        return '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><textarea data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="string">' + escapeHtml(value == null ? '' : value) + '</textarea></div>';
    }

    function renderSelectField(label, scope, path, value, options) {
        var html = '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><select data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="string">';

        options.forEach(function (option) {
            html += '<option value="' + escapeHtml(option.value) + '"' + (String(option.value) === String(value) ? ' selected' : '') + '>' + escapeHtml(option.label) + '</option>';
        });

        html += '</select></div>';
        return html;
    }

    function renderBlockCard() {
        var html = '';
        var elements = getElements();

        html += '<div class="nbde-inline-note">Элементы добавляются на живой холст и сразу становятся редактируемыми без отдельного preview-этапа.</div>';
        html += '<div class="nbde-action-grid">';
        html += '<button class="nbde-mini-button" type="button" data-action="duplicate-element">Дублировать</button>';
        html += '<button class="nbde-danger-button" type="button" data-action="delete-element">Удалить</button>';
        html += '</div>';
        html += '<div class="nbde-inline-note">Всего элементов: ' + elements.length + '</div>';
        html += '<div class="nbde-palette-grid">';

        state.palette.forEach(function (item) {
            html += '<button class="nbde-palette-button nbde-palette-item" type="button" data-action="add-element" data-type="' + escapeHtml(item.type) + '">';
            html += '<strong>' + escapeHtml(item.label || getTypeLabel(item.type)) + '</strong>';
            html += '<small>' + escapeHtml(item.description || '') + '</small>';
            html += '</button>';
        });

        html += '</div>';
        if (nodes.blockCard) {
            nodes.blockCard.innerHTML = html;
        }
    }

    function renderStageCard() {
        var stage = currentStageConfig();
        var editorRuntime = currentEditorRuntime();
        var html = '';

        html += '<div class="nbde-inline-note">Текущий режим: ' + escapeHtml(getBreakpointLabel(currentBreakpoint())) + '</div>';
        html += '<div class="nbde-field-grid nbde-field-grid--2">';
        html += renderField('Ширина холста', 'stage', 'width', stage.width, 'number');
        html += renderField('Мин. высота', 'stage', 'minHeight', stage.minHeight, 'number');
        html += renderField('Внутренний отступ X', 'stage', 'paddingX', stage.paddingX, 'number');
        html += renderField('Внутренний отступ Y', 'stage', 'paddingY', stage.paddingY, 'number');
        html += renderField('Шаг сетки', 'runtime-editor', 'gridSize', editorRuntime.gridSize, 'number');
        html += renderField('Порог привязки', 'runtime-editor', 'snapThreshold', editorRuntime.snapThreshold, 'number');
        html += '</div>';
        html += '<label class="nbde-checkbox"><input type="checkbox" data-scope="runtime-editor" data-path="snapToGrid" data-kind="boolean" ' + (editorRuntime.snapToGrid ? 'checked' : '') + '>Привязывать при перетаскивании</label>';
        html += '<label class="nbde-checkbox"><input type="checkbox" data-scope="runtime-editor" data-path="showGuides" data-kind="boolean" ' + (editorRuntime.showGuides ? 'checked' : '') + '>Показывать направляющие</label>';
        html += '<label class="nbde-checkbox"><input type="checkbox" data-scope="runtime-editor" data-path="showColumnsGrid" data-kind="boolean" ' + (editorRuntime.showColumnsGrid ? 'checked' : '') + '>Показывать 12 колонок</label>';

        if (nodes.stageCard) {
            nodes.stageCard.innerHTML = html;
        }
    }

    function renderSectionCard() {
        var section = getPath(state.documentState.contract, 'content.section', {});
        var background = getPath(state.documentState.contract, 'design.section.background', {});
        var html = '';

        html += '<div class="nbde-field-grid">';
        html += renderField('Название секции', 'section-content', 'name', section.name || '', 'string');
        html += renderSelectField('Фон секции', 'section-background', 'mode', background.mode || 'solid', [
            { value: 'solid', label: 'Сплошной' },
            { value: 'gradient', label: 'Градиент' },
            { value: 'image', label: 'Изображение' }
        ]);
        html += renderField('Цвет фона', 'section-background', 'color', background.color || '#f5f7fb', 'string');
        html += renderField('Градиент: от', 'section-background', 'gradientFrom', background.gradientFrom || '#f8fafc', 'string');
        html += renderField('Градиент: до', 'section-background', 'gradientTo', background.gradientTo || '#e2e8f0', 'string');
        html += renderField('Угол градиента', 'section-background', 'gradientAngle', background.gradientAngle || 135, 'number');
        html += renderField('Фоновое изображение', 'section-background', 'image', background.image || '', 'string');
        html += '</div>';

        if (nodes.sectionCard) {
            nodes.sectionCard.innerHTML = html;
        }
    }

    function renderLayersCard() {
        var elements = getElements().slice();
        var html = '';

        elements.sort(function (left, right) {
            var leftBox = resolveBranch(left, currentBreakpoint()).box;
            var rightBox = resolveBranch(right, currentBreakpoint()).box;
            return Number(rightBox.zIndex || 0) - Number(leftBox.zIndex || 0);
        });

        if (nodes.layersSummary) {
            nodes.layersSummary.textContent = elements.length + ' элементов';
        }

        if (!elements.length) {
            if (nodes.layersCard) {
                nodes.layersCard.innerHTML = '<div class="nbde-card__empty">Палитра справа добавляет первый элемент на живой холст.</div>';
            }
            return;
        }

        html += '<div class="nbde-layer-list">';
        elements.forEach(function (element) {
            var active = String(state.uiState.selectedElementId || '') === String(element.id);
            html += '<button class="nbde-layer-button' + (active ? ' is-active' : '') + '" type="button" data-action="select-element" data-element-id="' + escapeHtml(element.id) + '">';
            html += '<span class="nbde-layer-meta">';
            html += '<strong>' + escapeHtml(element.name || getTypeLabel(element.type)) + '</strong>';
            html += '<span>' + escapeHtml(getTypeLabel(element.type)) + '</span>';
            html += '</span>';
            html += '</button>';
        });
        html += '</div>';
        html += '<div class="nbde-action-grid">';
        html += '<button class="nbde-mini-button" type="button" data-action="move-layer-backward">Ниже</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="move-layer-forward">Выше</button>';
        html += '</div>';

        if (nodes.layersCard) {
            nodes.layersCard.innerHTML = html;
        }
    }

    function renderTypeSpecificFields(type, props) {
        var html = '<div class="nbde-field-grid">';

        if (type === 'text') {
            html += renderTextareaField('Текст', 'element-props', 'text', props.text || '');
            html += '<div class="nbde-field-grid nbde-field-grid--2">';
            html += renderField('Цвет', 'element-props', 'color', props.color || '#0f172a', 'string');
            html += renderField('Размер шрифта', 'element-props', 'fontSize', props.fontSize || 36, 'number');
            html += renderField('Насыщенность', 'element-props', 'fontWeight', props.fontWeight || 800, 'number');
            html += renderField('Межстрочный %', 'element-props', 'lineHeight', props.lineHeight || 120, 'number');
            html += renderField('Трекинг', 'element-props', 'letterSpacing', props.letterSpacing || 0, 'number');
            html += renderSelectField('Выравнивание', 'element-props', 'textAlign', props.textAlign || 'left', [
                { value: 'left', label: 'Слева' },
                { value: 'center', label: 'По центру' },
                { value: 'right', label: 'Справа' }
            ]);
            html += '</div>';
        } else if (type === 'button') {
            html += renderTextareaField('Текст кнопки', 'element-props', 'text', props.text || 'Нажмите сюда');
            html += '<div class="nbde-field-grid nbde-field-grid--2">';
            html += renderField('Ссылка', 'element-props', 'url', props.url || '#', 'string');
            html += renderField('Цвет текста', 'element-props', 'color', props.color || '#ffffff', 'string');
            html += renderField('Цвет кнопки', 'element-props', 'backgroundColor', props.backgroundColor || '#0f172a', 'string');
            html += renderField('Размер шрифта', 'element-props', 'fontSize', props.fontSize || 16, 'number');
            html += renderField('Насыщенность', 'element-props', 'fontWeight', props.fontWeight || 700, 'number');
            html += renderField('Скругление', 'element-props', 'borderRadius', props.borderRadius || 999, 'number');
            html += '</div>';
        } else if (type === 'image' || type === 'svg') {
            html += renderField('Файл', 'element-props', 'src', props.src || '', 'string');
            html += renderField('Alt', 'element-props', 'alt', props.alt || '', 'string');
            html += renderSelectField('Object fit', 'element-props', 'objectFit', props.objectFit || 'cover', [
                { value: 'cover', label: 'Cover' },
                { value: 'contain', label: 'Contain' },
                { value: 'fill', label: 'Fill' }
            ]);
        } else if (type === 'video') {
            html += renderField('Видео файл', 'element-props', 'src', props.src || '', 'string');
            html += renderField('Постер', 'element-props', 'poster', props.poster || '', 'string');
            html += renderSelectField('Object fit', 'element-props', 'objectFit', props.objectFit || 'cover', [
                { value: 'cover', label: 'Cover' },
                { value: 'contain', label: 'Contain' },
                { value: 'fill', label: 'Fill' }
            ]);
        } else if (type === 'shape') {
            html += renderField('Заливка', 'element-props', 'backgroundColor', props.backgroundColor || props.fill || '#f97316', 'string');
        } else if (type === 'icon') {
            html += renderField('Класс иконки', 'element-props', 'iconClass', props.iconClass || 'fas fa-star', 'string');
            html += renderField('Цвет', 'element-props', 'color', props.color || '#0f172a', 'string');
            html += renderField('Размер', 'element-props', 'size', props.size || 32, 'number');
        } else if (type === 'divider') {
            html += renderField('Цвет', 'element-props', 'backgroundColor', props.backgroundColor || props.color || '#cbd5e1', 'string');
            html += renderSelectField('Ориентация', 'element-props', 'orientation', props.orientation || 'horizontal', [
                { value: 'horizontal', label: 'Горизонтально' },
                { value: 'vertical', label: 'Вертикально' }
            ]);
        } else if (type === 'container') {
            html += '<div class="nbde-field-grid nbde-field-grid--2">';
            html += renderField('Отступ сверху', 'element-props', 'paddingTop', props.paddingTop || 20, 'number');
            html += renderField('Отступ справа', 'element-props', 'paddingRight', props.paddingRight || 20, 'number');
            html += renderField('Отступ снизу', 'element-props', 'paddingBottom', props.paddingBottom || 20, 'number');
            html += renderField('Отступ слева', 'element-props', 'paddingLeft', props.paddingLeft || 20, 'number');
            html += renderField('Фон контейнера', 'element-props', 'backgroundColor', props.backgroundColor || '', 'string');
            html += renderField('Внутренний gap', 'element-props', 'gap', props.gap || 16, 'number');
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    function renderPropertiesCard() {
        var element = getSelectedElement();
        var html = '';
        var branch;
        var props;
        var box;

        if (!element) {
            if (nodes.propertiesSummary) {
                nodes.propertiesSummary.textContent = 'Ничего не выбрано';
            }
            if (nodes.propertiesCard) {
                nodes.propertiesCard.innerHTML = '<div class="nbde-card__empty">Выберите элемент на холсте или в списке слоёв.</div>';
            }
            return;
        }

        branch = currentEditableBranch(element);
        props = branch.props || {};
        box = branch.box || {};

        if (nodes.propertiesSummary) {
            nodes.propertiesSummary.textContent = (element.name || getTypeLabel(element.type)) + ' • ' + getTypeLabel(element.type);
        }

        html += '<div class="nbde-field-grid nbde-field-grid--2">';
        html += renderField('Имя элемента', 'element-root', 'name', element.name || '', 'string');
        html += renderField('Роль', 'element-root', 'role', element.role || '', 'string');
        html += renderField('X', 'element-box', 'x', box.x || 0, 'number');
        html += renderField('Y', 'element-box', 'y', box.y || 0, 'number');
        html += renderField('Ширина', 'element-box', 'w', box.w || 0, 'number');
        html += renderField('Высота', 'element-box', 'h', box.h || 0, 'number');
        html += renderField('Слой', 'element-box', 'zIndex', box.zIndex || 1, 'number');
        html += renderField('Непрозрачность %', 'element-props', 'opacityPct', props.opacityPct || 100, 'number');
        html += renderField('Скругление', 'element-props', 'borderRadius', props.borderRadius || 0, 'number');
        html += renderField('Граница', 'element-props', 'borderWidth', props.borderWidth || 0, 'number');
        html += renderField('Цвет границы', 'element-props', 'borderColor', props.borderColor || '', 'string');
        html += renderField('Тень', 'element-props', 'boxShadow', props.boxShadow || '', 'string');
        html += '</div>';
        html += renderTypeSpecificFields(element.type, props);

        if (nodes.propertiesCard) {
            nodes.propertiesCard.innerHTML = html;
        }
    }

    function buildBackgroundStyle(background) {
        var parts = [];
        var mode = background.mode || 'solid';

        if (mode === 'gradient') {
            parts.push('background: linear-gradient(' + Number(background.gradientAngle || 135) + 'deg,' + String(background.gradientFrom || '#f8fafc') + ',' + String(background.gradientTo || '#e2e8f0') + ')');
        } else if (mode === 'image' && background.image) {
            parts.push('background-image: url("' + String(background.image).replace(/"/g, '\\"') + '")');
            parts.push('background-position:' + String(background.imagePosition || 'center center'));
            parts.push('background-size:' + String(background.imageSize || 'cover'));
            parts.push('background-repeat:' + String(background.imageRepeat || 'no-repeat'));
            parts.push('background-color:' + String(background.color || '#f5f7fb'));
        } else {
            parts.push('background:' + String(background.color || '#f5f7fb'));
        }

        return parts.join(';');
    }

    function buildCommonBodyStyle(props) {
        var styles = [];

        if (props.backgroundColor) {
            styles.push('background:' + String(props.backgroundColor));
        }

        if (props.borderRadius) {
            styles.push('border-radius:' + Number(props.borderRadius) + 'px');
        }

        if (props.borderWidth) {
            styles.push('border:' + Number(props.borderWidth) + 'px ' + String(props.borderStyle || 'solid') + ' ' + String(props.borderColor || '#cbd5e1'));
        }

        if (props.boxShadow) {
            styles.push('box-shadow:' + String(props.boxShadow));
        }

        if (props.opacityPct !== undefined) {
            styles.push('opacity:' + Math.max(0, Math.min(100, Number(props.opacityPct || 100))) / 100);
        }

        if (props.blur) {
            styles.push('filter: blur(' + Number(props.blur) + 'px)');
        }

        if (props.backdropBlur) {
            styles.push('backdrop-filter: blur(' + Number(props.backdropBlur) + 'px)');
        }

        if (props.rotate) {
            styles.push('transform: rotate(' + Number(props.rotate) + 'deg)');
            styles.push('transform-origin: center center');
        }

        return styles.join(';');
    }

    function renderElementHtml(element, breakpoint) {
        var branch = resolveBranch(element, breakpoint);
        var box = branch.box || {};
        var props = branch.props || {};
        var selected = String(state.uiState.selectedElementId || '') === String(element.id);
        var classes = 'nbde-el nbde-el--' + escapeHtml(element.type) + (selected ? ' is-selected' : '') + (element.hidden ? ' is-hidden' : '');
        var style = [
            'left:' + Number(box.x || 0) + 'px',
            'top:' + Number(box.y || 0) + 'px',
            'width:' + Math.max(1, Number(box.w || 1)) + 'px',
            'height:' + Math.max(1, Number(box.h || 1)) + 'px',
            'z-index:' + Number(box.zIndex || 1)
        ];
        var html = '<div class="' + classes + '" data-element-id="' + escapeHtml(element.id) + '" data-element-type="' + escapeHtml(element.type) + '" style="' + style.join(';') + '">';

        html += '<button class="nbde-el__drag" type="button" data-action="drag-element">Перетащить</button>';

        if (element.type === 'text') {
            html += '<div class="nbde-el__body nbde-el__body--text" contenteditable="true" spellcheck="false" data-inline-edit="text" style="' + escapeHtml(buildCommonBodyStyle(props) + ';color:' + String(props.color || '#0f172a') + ';font-size:' + Number(props.fontSize || 36) + 'px;font-weight:' + Number(props.fontWeight || 800) + ';line-height:' + (Number(props.lineHeight || 120) / 100) + ';letter-spacing:' + Number(props.letterSpacing || 0) + 'px;text-align:' + String(props.textAlign || 'left')) + '">' + textToHtml(props.text || '') + '</div>';
        } else if (element.type === 'button') {
            html += '<div class="nbde-el__body nbde-el__body--button" style="' + escapeHtml(buildCommonBodyStyle(props) + ';color:' + String(props.color || '#ffffff') + ';font-size:' + Number(props.fontSize || 16) + 'px;font-weight:' + Number(props.fontWeight || 700) + ';background:' + String(props.backgroundColor || '#0f172a')) + '"><span class="nbde-el__button-label" contenteditable="true" spellcheck="false" data-inline-edit="text">' + textToHtml(props.text || 'Нажмите сюда') + '</span></div>';
        } else if (element.type === 'image' || element.type === 'svg') {
            html += '<div class="nbde-el__body nbde-el__body--' + escapeHtml(element.type) + '" style="' + escapeHtml(buildCommonBodyStyle(props)) + '">';
            if (props.src) {
                html += '<img src="' + escapeHtml(props.src) + '" alt="' + escapeHtml(props.alt || '') + '" style="object-fit:' + escapeHtml(props.objectFit || 'cover') + ';border-radius:' + Number(props.borderRadius || 0) + 'px">';
            } else {
                html += '<div class="nbde-el__placeholder">Задайте файл в свойствах элемента</div>';
            }
            html += '</div>';
        } else if (element.type === 'video') {
            html += '<div class="nbde-el__body nbde-el__body--video" style="' + escapeHtml(buildCommonBodyStyle(props) + ';background:' + String(props.backgroundColor || '#0f172a')) + '"><div class="nbde-el__placeholder">' + escapeHtml(props.src ? 'Видео подключено' : 'Укажите видео файл') + '</div></div>';
        } else if (element.type === 'shape') {
            html += '<div class="nbde-el__body" style="' + escapeHtml(buildCommonBodyStyle(props) + ';background:' + String(props.backgroundColor || props.fill || '#f97316')) + '"></div>';
        } else if (element.type === 'icon') {
            html += '<div class="nbde-el__body nbde-el__body--icon" style="' + escapeHtml(buildCommonBodyStyle(props) + ';color:' + String(props.color || '#0f172a') + ';font-size:' + Number(props.size || 32) + 'px') + '"><i class="' + escapeHtml(props.iconClass || 'fas fa-star') + '"></i></div>';
        } else if (element.type === 'divider') {
            html += '<div class="nbde-el__body nbde-el__body--divider" style="' + escapeHtml(buildCommonBodyStyle(props) + ';background:' + String(props.backgroundColor || props.color || '#cbd5e1')) + '"></div>';
        } else if (element.type === 'container') {
            html += '<div class="nbde-el__body" style="' + escapeHtml(buildCommonBodyStyle(props)) + '"></div>';
            html += '<div class="nbde-el__children-host" style="top:' + Number(props.paddingTop || 0) + 'px;right:' + Number(props.paddingRight || 0) + 'px;bottom:' + Number(props.paddingBottom || 0) + 'px;left:' + Number(props.paddingLeft || 0) + 'px;">';
            (element.children || []).forEach(function (child) {
                html += renderElementHtml(child, breakpoint);
            });
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    function renderCanvas() {
        var contract = state.documentState.contract;
        var tree = buildTree(getElements());
        var stage = currentStageConfig();
        var editorRuntime = currentEditorRuntime();
        var background = getPath(contract, 'design.section.background', {});
        var html = '';
        var columnIndex;

        html += '<div class="nbde-stage" id="nbd-stage-scene" style="--nbde-stage-width:' + Number(stage.width || 1200) + 'px;--nbde-stage-height:' + Number(stage.minHeight || 680) + 'px;">';
        html += '<div class="nbde-stage__surface" style="' + escapeHtml(buildBackgroundStyle(background)) + '">';

        if (editorRuntime.showColumnsGrid) {
            html += '<div class="nbde-stage__columns">';
            for (columnIndex = 0; columnIndex < Number(editorRuntime.columnsCount || 12); columnIndex++) {
                html += '<span></span>';
            }
            html += '</div>';
        }

        if (editorRuntime.showGuides) {
            html += '<div class="nbde-stage__guides">';
            if (state.interactionState.guideX != null) {
                html += '<div class="nbde-stage__guide nbde-stage__guide--x" style="left:' + Number(state.interactionState.guideX) + 'px"></div>';
            }
            if (state.interactionState.guideY != null) {
                html += '<div class="nbde-stage__guide nbde-stage__guide--y" style="top:' + Number(state.interactionState.guideY) + 'px"></div>';
            }
            html += '</div>';
        }

        html += '<div class="nbde-stage__scene">';

        if (!tree.length) {
            html += '<div class="nbde-stage__empty">Добавьте элемент из палитры справа, затем перетаскивайте его по холсту и редактируйте текст прямо здесь.</div>';
        } else {
            tree.forEach(function (element) {
                html += renderElementHtml(element, currentBreakpoint());
            });
        }

        html += '</div></div></div>';
        if (nodes.canvasStage) {
            nodes.canvasStage.innerHTML = html;
        }
        updateCanvasMeta();
    }

    function renderAll() {
        ensureSelectedElement();
        renderTopbar();
        renderBlockCard();
        renderStageCard();
        renderSectionCard();
        renderLayersCard();
        renderPropertiesCard();
        renderCanvas();
    }

    function applyScopedInput(input) {
        var scope = input.dataset.scope;
        var path = input.dataset.path;
        var value = coerceValue(input);
        var element = getSelectedElement();
        var branch;

        if (!scope || !path) {
            return false;
        }

        if (scope === 'stage') {
            setPath(state.documentState.contract.layout.stage[currentBreakpoint()], path, value);
            return true;
        }

        if (scope === 'runtime-editor') {
            setPath(state.documentState.contract.runtime.editor, path, value);
            return true;
        }

        if (scope === 'section-content') {
            setPath(state.documentState.contract.content.section, path, value);
            return true;
        }

        if (scope === 'section-background') {
            setPath(state.documentState.contract.design.section.background, path, value);
            return true;
        }

        if (!element) {
            return false;
        }

        branch = currentEditableBranch(element);

        if (scope === 'element-root') {
            setPath(element, path, value);
            return true;
        }

        if (scope === 'element-box') {
            setPath(branch.box, path, value);
            return true;
        }

        if (scope === 'element-props') {
            setPath(branch.props, path, value);
            return true;
        }

        return false;
    }

    function nextElementId(type) {
        var index = getElements().length + 1;
        var candidate = String(type || 'element') + '_' + index;

        while (getElementById(candidate)) {
            index += 1;
            candidate = String(type || 'element') + '_' + index;
        }

        return candidate;
    }

    function addElement(type) {
        var elements = getElements();
        var stage = currentStageConfig();
        var branch = defaultBranch(type);
        var offset = elements.length * 18;
        var element = normalizeElement({
            id: nextElementId(type),
            type: type,
            name: getTypeLabel(type),
            desktop: branch,
            tablet: clone(branch),
            mobile: clone(branch)
        }, elements.length);

        element.desktop.box.x = Number(stage.paddingX || 24) + offset;
        element.desktop.box.y = Number(stage.paddingY || 24) + offset;
        element.desktop.box.zIndex = elements.length + 1;
        element.tablet.box.x = element.desktop.box.x;
        element.tablet.box.y = element.desktop.box.y;
        element.mobile.box.x = element.desktop.box.x;
        element.mobile.box.y = element.desktop.box.y;

        elements.push(element);
        state.uiState.selectedElementId = element.id;
        markDirty();
        renderAll();
    }

    function collectDescendantIds(elementId) {
        var ids = [String(elementId)];
        var changed = true;

        while (changed) {
            changed = false;
            getElements().forEach(function (element) {
                if (ids.indexOf(String(element.parentId || '')) >= 0 && ids.indexOf(String(element.id)) === -1) {
                    ids.push(String(element.id));
                    changed = true;
                }
            });
        }

        return ids;
    }

    function deleteSelectedElement() {
        var selected = getSelectedElement();
        var ids;
        var elements;

        if (!selected) {
            return;
        }

        ids = collectDescendantIds(selected.id);
        elements = getElements().filter(function (element) {
            return ids.indexOf(String(element.id)) === -1;
        });

        state.documentState.contract.content.section.elements = elements;
        state.uiState.selectedElementId = elements.length ? elements[0].id : null;
        markDirty();
        renderAll();
    }

    function duplicateSelectedElement() {
        var selected = getSelectedElement();
        var copy;

        if (!selected) {
            return;
        }

        copy = clone(selected);
        copy.id = nextElementId(copy.type || 'element');
        copy.name = (copy.name || getTypeLabel(copy.type)) + ' копия';
        copy.parentId = selected.parentId || '';
        copy.desktop.box.x = Number(copy.desktop.box.x || 0) + 24;
        copy.desktop.box.y = Number(copy.desktop.box.y || 0) + 24;
        copy.tablet.box.x = Number(copy.tablet.box.x || 0) + 24;
        copy.tablet.box.y = Number(copy.tablet.box.y || 0) + 24;
        copy.mobile.box.x = Number(copy.mobile.box.x || 0) + 24;
        copy.mobile.box.y = Number(copy.mobile.box.y || 0) + 24;

        state.documentState.contract.content.section.elements.push(copy);
        state.uiState.selectedElementId = copy.id;
        markDirty();
        renderAll();
    }

    function moveSelectedElement(delta) {
        var selected = getSelectedElement();
        var elements = getElements();
        var index;
        var targetIndex;

        if (!selected) {
            return;
        }

        for (index = 0; index < elements.length; index++) {
            if (String(elements[index].id) === String(selected.id)) {
                targetIndex = Math.max(0, Math.min(elements.length - 1, index + delta));
                if (targetIndex === index) {
                    return;
                }

                elements.splice(targetIndex, 0, elements.splice(index, 1)[0]);
                elements.forEach(function (element, zIndex) {
                    element.desktop.box.zIndex = zIndex + 1;
                    element.tablet.box.zIndex = zIndex + 1;
                    element.mobile.box.zIndex = zIndex + 1;
                    currentEditableBranch(element).box.zIndex = zIndex + 1;
                });
                markDirty();
                renderAll();
                return;
            }
        }
    }

    function normalizeInlineText(node) {
        return String(node.innerText || node.textContent || '').replace(/\r/g, '').replace(/\n{3,}/g, '\n\n').replace(/\s+$/g, '');
    }

    function snapValue(value, candidates, threshold) {
        var best = value;
        var bestDistance = Number(threshold) + 1;

        candidates.forEach(function (candidate) {
            var distance = Math.abs(Number(candidate) - Number(value));
            if (distance <= threshold && distance < bestDistance) {
                best = Number(candidate);
                bestDistance = distance;
            }
        });

        return best;
    }

    function buildCandidates(length, step, count) {
        var result = [];
        var index;
        var size = Math.max(1, Number(step || 8));
        var columns = Math.max(1, Number(count || 12));

        for (index = 0; index <= length; index += size) {
            result.push(index);
        }

        for (index = 0; index <= columns; index++) {
            result.push(Math.round((length / columns) * index));
        }

        return result;
    }

    function beginDrag(elementId, event) {
        var stageScene = document.getElementById('nbd-stage-scene');
        var wrapper = event.target.closest('.nbde-el');
        var element = getElementById(elementId);
        var branch = element ? currentEditableBranch(element) : null;
        var host = wrapper ? wrapper.parentElement : null;

        if (!stageScene || !wrapper || !element || !branch || !host) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        state.uiState.selectedElementId = elementId;
        state.interactionState.drag = {
            elementId: elementId,
            startClientX: event.clientX,
            startClientY: event.clientY,
            startX: Number(branch.box.x || 0),
            startY: Number(branch.box.y || 0),
            hostRect: host.getBoundingClientRect(),
            stageRect: stageScene.getBoundingClientRect()
        };

        renderLayersCard();
        renderPropertiesCard();
        renderCanvas();
    }

    function handleDragMove(event) {
        var drag = state.interactionState.drag;
        var editorRuntime = currentEditorRuntime();
        var element;
        var branch;
        var hostWidth;
        var hostHeight;
        var nextX;
        var nextY;
        var xCandidates;
        var yCandidates;

        if (!drag) {
            return;
        }

        element = getElementById(drag.elementId);
        branch = element ? currentEditableBranch(element) : null;

        if (!element || !branch) {
            return;
        }

        hostWidth = Math.max(1, Math.round(drag.hostRect.width));
        hostHeight = Math.max(1, Math.round(drag.hostRect.height));
        nextX = drag.startX + (event.clientX - drag.startClientX);
        nextY = drag.startY + (event.clientY - drag.startClientY);

        if (editorRuntime.snapToGrid) {
            xCandidates = buildCandidates(hostWidth, editorRuntime.gridSize, editorRuntime.columnsCount);
            yCandidates = buildCandidates(hostHeight, editorRuntime.gridSize, 12);
            nextX = snapValue(nextX, xCandidates, Number(editorRuntime.snapThreshold || 6));
            nextY = snapValue(nextY, yCandidates, Number(editorRuntime.snapThreshold || 6));
        }

        branch.box.x = nextX;
        branch.box.y = nextY;
        state.interactionState.guideX = Math.max(0, Math.round((drag.hostRect.left - drag.stageRect.left) + nextX));
        state.interactionState.guideY = Math.max(0, Math.round((drag.hostRect.top - drag.stageRect.top) + nextY));
        markDirty();
        renderCanvas();
    }

    function finishDrag() {
        if (!state.interactionState.drag) {
            return;
        }

        state.interactionState.drag = null;
        clearGuides();
        renderCanvas();
    }

    async function saveContract() {
        var response;
        var payload;

        if (!state.editor.saveUrl) {
            return;
        }

        if (!state.documentState.contract || !state.documentState.block) {
            return;
        }

        state.uiState.isSaving = true;
        state.uiState.lastError = '';
        renderStatus();

        try {
            response = await fetch(state.editor.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    title: state.documentState.block.title || '',
                    contract: state.documentState.contract,
                    csrf_token: state.editor.csrfToken
                })
            });
            payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload.error || 'save_failed');
            }

            state.documentState.contract = normalizeContract(payload.contract || state.documentState.contract);
            state.documentState.lastSavedAt = new Date().toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            state.uiState.isDirty = false;
            state.uiState.lastError = '';
            renderAll();
        } catch (error) {
            state.uiState.lastError = 'Ошибка сохранения: ' + (error && error.message ? error.message : 'неизвестная ошибка');
            renderStatus();
        } finally {
            state.uiState.isSaving = false;
            renderStatus();
        }
    }

    async function loadState() {
        var response;
        var payload;

        if (!state.editor.stateUrl) {
            state.uiState.lastError = 'Не задан URL состояния редактора';
            renderStatus();
            return;
        }

        state.uiState.lastError = '';
        renderStatus();

        try {
            response = await fetch(state.editor.stateUrl, {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload.error || 'state_load_failed');
            }

            state.documentState.block = payload.block || null;
            state.documentState.contract = normalizeContract(payload.contract || {});
            state.palette = getPath(payload, 'palette.items', []);
            state.pickers = payload.pickers || {};
            state.editor.saveUrl = getPath(payload, 'editor.saveUrl', state.editor.saveUrl);
            state.editor.placeUrl = getPath(payload, 'editor.placeUrl', state.editor.placeUrl);
            state.editor.backUrl = getPath(payload, 'editor.backUrl', state.editor.backUrl);
            state.editor.csrfToken = getPath(payload, 'editor.csrfToken', state.editor.csrfToken);
            state.uiState.activeBreakpoint = getPath(payload, 'ui.activeBreakpoint', state.uiState.activeBreakpoint) || 'desktop';
            state.uiState.selectedElementId = getPath(payload, 'ui.selectedElementId', null);
            state.uiState.isDirty = false;
            state.uiState.lastError = '';
            clearGuides();

            renderAll();
        } catch (error) {
            state.uiState.lastError = 'Ошибка загрузки редактора: ' + (error && error.message ? error.message : 'неизвестная ошибка');
            renderStatus();
        }
    }

    root.addEventListener('click', function (event) {
        var actionNode = event.target.closest('[data-action]');
        var elementNode = event.target.closest('.nbde-el');
        var action;

        if (actionNode) {
            action = actionNode.dataset.action;

            if (action === 'add-element') {
                addElement(actionNode.dataset.type || 'text');
                return;
            }

            if (action === 'reload-state') {
                loadState();
                return;
            }

            if (action === 'select-element') {
                state.uiState.selectedElementId = actionNode.dataset.elementId || null;
                renderLayersCard();
                renderPropertiesCard();
                renderCanvas();
                return;
            }

            if (action === 'delete-element') {
                deleteSelectedElement();
                return;
            }

            if (action === 'duplicate-element') {
                duplicateSelectedElement();
                return;
            }

            if (action === 'move-layer-forward') {
                moveSelectedElement(1);
                return;
            }

            if (action === 'move-layer-backward') {
                moveSelectedElement(-1);
                return;
            }
        }

        if (elementNode) {
            state.uiState.selectedElementId = elementNode.dataset.elementId || null;
            renderLayersCard();
            renderPropertiesCard();
            renderCanvas();
        }
    });

    root.addEventListener('input', function (event) {
        var target = event.target;
        var inlineElement;
        var wrapper;

        if (target === nodes.titleInput) {
            if (state.documentState.block) {
                state.documentState.block.title = target.value;
            }
            markDirty();
            return;
        }

        if (target.dataset.inlineEdit === 'text') {
            wrapper = target.closest('.nbde-el');
            inlineElement = getElementById(wrapper ? wrapper.dataset.elementId || '' : '');
            if (!inlineElement) {
                return;
            }

            currentEditableBranch(inlineElement).props.text = normalizeInlineText(target);
            state.uiState.selectedElementId = inlineElement.id;
            markDirty();
            renderPropertiesCard();
            return;
        }

        if (applyScopedInput(target)) {
            if (target.dataset.scope === 'element-root' && target.dataset.path === 'name') {
                renderLayersCard();
            }
            markDirty();
            renderCanvas();
        }
    });

    root.addEventListener('change', function (event) {
        var target = event.target;

        if (applyScopedInput(target)) {
            if (target.dataset.scope === 'element-root' && target.dataset.path === 'name') {
                renderLayersCard();
            }
            markDirty();
            renderCanvas();
        }
    });

    root.addEventListener('pointerdown', function (event) {
        var dragNode = event.target.closest('[data-action="drag-element"]');
        var wrapper = event.target.closest('.nbde-el');

        if (!dragNode || !wrapper) {
            return;
        }

        beginDrag(wrapper.dataset.elementId || '', event);
    });

    document.addEventListener('pointermove', handleDragMove);
    document.addEventListener('pointerup', finishDrag);
    document.addEventListener('pointercancel', finishDrag);

    if (nodes.saveButton) {
        nodes.saveButton.addEventListener('click', function () {
            saveContract();
        });
    }

    window.addEventListener('keydown', function (event) {
        var active;
        var tagName;
        var editable;

        if ((event.ctrlKey || event.metaKey) && String(event.key || '').toLowerCase() === 's') {
            event.preventDefault();
            saveContract();
            return;
        }

        if (String(event.key || '') === 'Delete') {
            active = document.activeElement;
            tagName = active && active.tagName ? active.tagName.toLowerCase() : '';
            editable = !!(active && active.isContentEditable);

            if (editable || tagName === 'input' || tagName === 'textarea' || tagName === 'select') {
                return;
            }

            deleteSelectedElement();
        }
    });

    Array.prototype.forEach.call(root.querySelectorAll('[data-breakpoint]'), function (button) {
        button.addEventListener('click', function () {
            state.uiState.activeBreakpoint = button.dataset.breakpoint || 'desktop';
            clearGuides();
            renderAll();
        });
    });

    loadState();
})();
