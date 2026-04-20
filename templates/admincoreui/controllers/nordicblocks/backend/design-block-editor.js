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
        svg: 'SVG',
        group: 'Группа'
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
            selectionIds: [],
            selectedElementId: null,
            editingTextId: null,
            pendingFocusTextId: null,
            isDirty: false,
            isSaving: false,
            lastError: ''
        },
        interactionState: {
            guideX: null,
            guideY: null,
            drag: null,
            resize: null
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

            return Number.isFinite(Number(input.value)) ? Number(input.value) : 0;
        }

        return input.value;
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(Number(value), Number(min)), Number(max));
    }

    function selectorEscape(value) {
        return String(value == null ? '' : value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
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

    function isEditableType(type) {
        return type === 'text' || type === 'button';
    }

    function isMediaType(type) {
        return type === 'image' || type === 'svg' || type === 'video';
    }

    function getColorInputValue(value, fallback) {
        var normalized = String(value || '').trim();

        if (/^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test(normalized)) {
            return normalized.length === 4
                ? '#' + normalized.charAt(1) + normalized.charAt(1) + normalized.charAt(2) + normalized.charAt(2) + normalized.charAt(3) + normalized.charAt(3)
                : normalized;
        }

        return fallback;
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
        } else if (type === 'group') {
            branch.box.w = 360;
            branch.box.h = 220;
            branch.props.label = 'Группа';
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
        normalized.content.section.elements = normalized.content.section.elements.map(function (element, index) {
            return normalizeElement(element, index);
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
            columnsCount: 12,
            columnsGridColor: '#0f172a',
            columnsGridOpacity: 8
        }, normalized.runtime.editor || {});

        return normalized;
    }

    function getElements() {
        return getPath(state.documentState.contract, 'content.section.elements', []);
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

    function withEachBreakpoint(element, callback) {
        ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
            if (!element[breakpoint]) {
                element[breakpoint] = clone(element.desktop || defaultBranch(element.type));
            }
            callback(element[breakpoint], breakpoint);
        });
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

    function getSelectionIds() {
        var elements = getElements();
        var known = {};

        elements.forEach(function (element) {
            known[String(element.id)] = true;
        });

        return (state.uiState.selectionIds || []).filter(function (id, index, source) {
            return known[String(id)] && source.indexOf(id) === index;
        });
    }

    function getRootSelectionIds(ids) {
        var selectedIds = (ids || getSelectionIds()).map(String);
        return selectedIds.filter(function (id) {
            var element = getElementById(id);
            var parentId = element ? String(element.parentId || '') : '';

            while (parentId) {
                if (selectedIds.indexOf(parentId) >= 0) {
                    return false;
                }
                element = getElementById(parentId);
                parentId = element ? String(element.parentId || '') : '';
            }

            return true;
        });
    }

    function setSelection(ids, primaryId) {
        var normalized = (ids || []).map(String).filter(function (id, index, source) {
            return !!getElementById(id) && source.indexOf(id) === index;
        });

        state.uiState.selectionIds = normalized;
        state.uiState.selectedElementId = primaryId && normalized.indexOf(String(primaryId)) >= 0
            ? String(primaryId)
            : (normalized.length ? normalized[normalized.length - 1] : null);

        if (state.uiState.editingTextId && normalized.indexOf(String(state.uiState.editingTextId)) === -1) {
            state.uiState.editingTextId = null;
            state.uiState.pendingFocusTextId = null;
        }
    }

    function toggleSelection(id) {
        var current = getSelectionIds();
        var stringId = String(id || '');
        var index = current.indexOf(stringId);

        if (index >= 0) {
            current.splice(index, 1);
            setSelection(current, current.length ? current[current.length - 1] : null);
            return;
        }

        current.push(stringId);
        setSelection(current, stringId);
    }

    function clearSelection() {
        setSelection([], null);
    }

    function isSelected(id) {
        return getSelectionIds().indexOf(String(id)) >= 0;
    }

    function getSelectedElement() {
        return getElementById(state.uiState.selectedElementId || '');
    }

    function getSelectedElements() {
        return getSelectionIds().map(getElementById).filter(Boolean);
    }

    function ensureSelectionState() {
        var ids = getSelectionIds();
        var elements = getElements();

        if (ids.length) {
            setSelection(ids, state.uiState.selectedElementId);
            return;
        }

        if (elements.length) {
            setSelection([elements[0].id], elements[0].id);
            return;
        }

        clearSelection();
    }

    function markDirty() {
        state.uiState.isDirty = true;
        state.uiState.lastError = '';
        renderStatus();
    }

    function clearGuides() {
        state.interactionState.guideX = null;
        state.interactionState.guideY = null;
    }

    function getParentContentOffset(parent, breakpoint) {
        var branch = resolveBranch(parent, breakpoint);
        var props = branch.props || {};

        if (parent.type === 'container') {
            return {
                x: Number(props.paddingLeft || 0),
                y: Number(props.paddingTop || 0)
            };
        }

        return { x: 0, y: 0 };
    }

    function getAbsoluteBox(element, breakpoint) {
        var branch = resolveBranch(element, breakpoint);
        var box = branch.box || {};
        var absolute = {
            x: Number(box.x || 0),
            y: Number(box.y || 0),
            w: Math.max(1, Number(box.w || 1)),
            h: Math.max(1, Number(box.h || 1)),
            zIndex: Number(box.zIndex || 1)
        };
        var parent = element.parentId ? getElementById(element.parentId) : null;

        while (parent) {
            var parentBranch = resolveBranch(parent, breakpoint);
            var parentBox = parentBranch.box || {};
            var offset = getParentContentOffset(parent, breakpoint);
            absolute.x += Number(parentBox.x || 0) + offset.x;
            absolute.y += Number(parentBox.y || 0) + offset.y;
            parent = parent.parentId ? getElementById(parent.parentId) : null;
        }

        return absolute;
    }

    function buildSelectionBounds(selectionIds, breakpoint) {
        var ids = getRootSelectionIds(selectionIds);
        var bounds = null;

        ids.forEach(function (id) {
            var element = getElementById(id);
            var box;

            if (!element) {
                return;
            }

            box = getAbsoluteBox(element, breakpoint);

            if (!bounds) {
                bounds = {
                    x: box.x,
                    y: box.y,
                    right: box.x + box.w,
                    bottom: box.y + box.h
                };
                return;
            }

            bounds.x = Math.min(bounds.x, box.x);
            bounds.y = Math.min(bounds.y, box.y);
            bounds.right = Math.max(bounds.right, box.x + box.w);
            bounds.bottom = Math.max(bounds.bottom, box.y + box.h);
        });

        if (!bounds) {
            return null;
        }

        bounds.w = Math.max(1, bounds.right - bounds.x);
        bounds.h = Math.max(1, bounds.bottom - bounds.y);
        return bounds;
    }

    function getMinBoxSize(element, props) {
        var type = element && element.type ? String(element.type) : 'shape';
        var orientation = String((props && props.orientation) || 'horizontal');

        if (type === 'text') {
            return { w: 120, h: 32 };
        }

        if (type === 'button') {
            return { w: 96, h: 40 };
        }

        if (type === 'icon') {
            return { w: 24, h: 24 };
        }

        if (type === 'divider') {
            return orientation === 'vertical' ? { w: 1, h: 24 } : { w: 24, h: 1 };
        }

        if (type === 'group') {
            return { w: 64, h: 64 };
        }

        return { w: 24, h: 24 };
    }

    function getResizeHandles(element, props) {
        var type = element && element.type ? String(element.type) : 'shape';
        var orientation = String((props && props.orientation) || 'horizontal');

        if (type === 'text') {
            return ['w', 'e'];
        }

        if (type === 'divider') {
            return orientation === 'vertical' ? ['n', 's'] : ['w', 'e'];
        }

        if (isMediaType(type)) {
            return ['nw', 'ne', 'se', 'sw'];
        }

        return ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
    }

    function buildTree(elements) {
        var indexed = {};
        var tree = [];

        elements.forEach(function (element) {
            if (!element || !element.id) {
                return;
            }
            indexed[element.id] = clone(element);
            indexed[element.id].children = [];
        });

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

    function normalizeInlineText(node) {
        return String(node.innerText || node.textContent || '')
            .replace(/\r/g, '')
            .replace(/\n{3,}/g, '\n\n')
            .replace(/\s+$/g, '');
    }

    function syncInlineTextHeight(elementId, bodyNode, shouldPersist) {
        var element = getElementById(elementId || '');
        var branch;
        var wrapper;
        var measured;

        if (!element || element.type !== 'text' || !bodyNode) {
            return;
        }

        branch = currentEditableBranch(element);
        wrapper = bodyNode.closest('.nbde-el');
        measured = Math.max(32, Math.ceil(bodyNode.scrollHeight));

        if (wrapper) {
            wrapper.style.height = measured + 'px';
        }

        if (shouldPersist) {
            branch.box.h = measured;
        }
    }

    function requestInlineFocus(elementId) {
        if (!elementId) {
            state.uiState.editingTextId = null;
            state.uiState.pendingFocusTextId = null;
            return;
        }

        state.uiState.editingTextId = elementId;
        state.uiState.pendingFocusTextId = elementId;
    }

    function focusPendingInlineEditor() {
        var elementId = state.uiState.pendingFocusTextId;
        var editableNode;
        var selection;
        var range;

        if (!elementId || !nodes.canvasStage) {
            return;
        }

        editableNode = nodes.canvasStage.querySelector('.nbde-el[data-element-id="' + selectorEscape(elementId) + '"] [data-inline-edit="text"]');
        if (!editableNode) {
            return;
        }

        state.uiState.pendingFocusTextId = null;

        requestAnimationFrame(function () {
            editableNode.focus();
            selection = window.getSelection ? window.getSelection() : null;
            range = document.createRange ? document.createRange() : null;

            if (selection && range) {
                range.selectNodeContents(editableNode);
                range.collapse(false);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        });
    }

    function renderStatus() {
        var selectionCount = getSelectionIds().length;

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
            nodes.statusText.textContent = 'Изменения пока только в scene state. Сохраните, чтобы зафиксировать контракт.';
            nodes.statusText.classList.add('is-dirty');
            return;
        }

        if (state.documentState.lastSavedAt) {
            nodes.statusText.textContent = 'Сохранено: ' + state.documentState.lastSavedAt;
            return;
        }

        nodes.statusText.textContent = selectionCount > 1
            ? 'Выбрано ' + selectionCount + ' элементов. Можно группировать, дублировать и двигать их как набор.'
            : 'Редактор готов. Двойной клик по тексту включает inline edit прямо на холсте.';
    }

    function updateCanvasMeta() {
        var stage = currentStageConfig();
        var elements = getElements();
        var selectionCount = getSelectionIds().length;

        if (nodes.canvasMeta) {
            nodes.canvasMeta.textContent = getBreakpointLabel(currentBreakpoint()) + ' • ' + (stage.width || 0) + 'px • ' + (stage.minHeight || 0) + 'px • ' + elements.length + ' эл. • ' + selectionCount + ' выбрано';
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
        var selectionIds = getSelectionIds();
        var html = '';

        html += '<div class="nbde-inline-note">Canvas-first режим: drag, resize, duplicate, z-index и inline text editing идут через scene state, а не через form-only inspector.</div>';
        html += '<div class="nbde-action-grid">';
        html += '<button class="nbde-mini-button" type="button" data-action="duplicate-element">Дублировать</button>';
        html += '<button class="nbde-danger-button" type="button" data-action="delete-element">Удалить</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="group-selection"' + (selectionIds.length > 1 ? '' : ' disabled') + '>Группа</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="ungroup-selection">Разгруппа</button>';
        html += '</div>';
        html += '<div class="nbde-shortcuts">';
        html += '<span>Shift+клик: мультивыбор</span>';
        html += '<span>Ctrl+D: копия</span>';
        html += '<span>Delete: удалить</span>';
        html += '</div>';
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
        html += renderField('Цвет колонок', 'runtime-editor', 'columnsGridColor', editorRuntime.columnsGridColor || '#0f172a', 'string');
        html += renderField('Прозрачность колонок %', 'runtime-editor', 'columnsGridOpacity', editorRuntime.columnsGridOpacity == null ? 8 : editorRuntime.columnsGridOpacity, 'number');
        html += '</div>';
        html += '<label class="nbde-checkbox"><input type="checkbox" data-scope="runtime-editor" data-path="snapToGrid" data-kind="boolean" ' + (editorRuntime.snapToGrid ? 'checked' : '') + '>Привязывать при перемещении</label>';
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
        var selectionIds = getSelectionIds();

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
            var active = selectionIds.indexOf(String(element.id)) >= 0;
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
        html += '<button class="nbde-mini-button" type="button" data-action="send-to-back">Вниз</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="bring-to-front">Наверх</button>';
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
        } else if (type === 'group') {
            html += '<div class="nbde-card__empty">Группа управляется как общий transform-узел. Двигайте, масштабируйте и меняйте z-index на холсте.</div>';
        }

        html += '</div>';
        return html;
    }

    function renderPropertiesCard() {
        var selection = getSelectedElements();
        var element = getSelectedElement();
        var html = '';
        var branch;
        var props;
        var box;

        if (!selection.length || !element) {
            if (nodes.propertiesSummary) {
                nodes.propertiesSummary.textContent = 'Ничего не выбрано';
            }
            if (nodes.propertiesCard) {
                nodes.propertiesCard.innerHTML = '<div class="nbde-card__empty">Выберите элемент на холсте или в списке слоёв.</div>';
            }
            return;
        }

        if (selection.length > 1) {
            if (nodes.propertiesSummary) {
                nodes.propertiesSummary.textContent = selection.length + ' элементов';
            }
            if (nodes.propertiesCard) {
                nodes.propertiesCard.innerHTML = '<div class="nbde-card__empty">Мультивыбор активен. Для точных свойств выберите один узел, для группировки используйте toolbar или кнопку справа.</div>';
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

    function renderResizeHandles(element, props) {
        return getResizeHandles(element, props).map(function (handle) {
            return '<button class="nbde-el__handle nbde-el__handle--' + handle + '" type="button" data-action="resize-element" data-handle="' + handle + '" aria-label="Изменить размер"></button>';
        }).join('');
    }

    function renderElementHtml(element, breakpoint) {
        var branch = resolveBranch(element, breakpoint);
        var box = branch.box || {};
        var props = branch.props || {};
        var selected = isSelected(element.id);
        var primary = String(state.uiState.selectedElementId || '') === String(element.id);
        var editing = String(state.uiState.editingTextId || '') === String(element.id) && isEditableType(element.type);
        var classes = 'nbde-el nbde-el--' + escapeHtml(element.type) + (selected ? ' is-selected' : '') + (primary ? ' is-primary' : '') + (element.hidden ? ' is-hidden' : '');
        var style = [
            'left:' + Number(box.x || 0) + 'px',
            'top:' + Number(box.y || 0) + 'px',
            'width:' + Math.max(1, Number(box.w || 1)) + 'px',
            'height:' + Math.max(1, Number(box.h || 1)) + 'px',
            'z-index:' + Number(box.zIndex || 1)
        ];
        var html = '<div class="' + classes + '" data-element-id="' + escapeHtml(element.id) + '" data-element-type="' + escapeHtml(element.type) + '" style="' + style.join(';') + '">';

        if (primary) {
            html += '<button class="nbde-el__drag" type="button" data-action="drag-element">Перетащить</button>';
        }
        if (primary && getSelectionIds().length === 1) {
            html += renderResizeHandles(element, props);
        }

        if (element.type === 'text') {
            html += '<div class="nbde-el__body nbde-el__body--text' + (editing ? ' is-editing' : '') + '" contenteditable="' + (editing ? 'true' : 'false') + '" spellcheck="false" data-inline-edit="text" style="' + escapeHtml(buildCommonBodyStyle(props) + ';color:' + String(props.color || '#0f172a') + ';font-size:' + Number(props.fontSize || 36) + 'px;font-weight:' + Number(props.fontWeight || 800) + ';line-height:' + (Number(props.lineHeight || 120) / 100) + ';letter-spacing:' + Number(props.letterSpacing || 0) + 'px;text-align:' + String(props.textAlign || 'left')) + '">' + textToHtml(props.text || '') + '</div>';
        } else if (element.type === 'button') {
            html += '<div class="nbde-el__body nbde-el__body--button" style="' + escapeHtml(buildCommonBodyStyle(props) + ';color:' + String(props.color || '#ffffff') + ';font-size:' + Number(props.fontSize || 16) + 'px;font-weight:' + Number(props.fontWeight || 700) + ';background:' + String(props.backgroundColor || '#0f172a')) + '"><span class="nbde-el__button-label' + (editing ? ' is-editing' : '') + '" contenteditable="' + (editing ? 'true' : 'false') + '" spellcheck="false" data-inline-edit="text">' + textToHtml(props.text || 'Нажмите сюда') + '</span></div>';
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
        } else if (element.type === 'container' || element.type === 'group') {
            html += '<div class="nbde-el__body nbde-el__body--group" style="' + escapeHtml(buildCommonBodyStyle(props)) + '"></div>';
            if (element.type === 'group') {
                html += '<div class="nbde-group-label">Group</div>';
            }
            html += '<div class="nbde-el__children-host" style="top:' + Number(element.type === 'container' ? (props.paddingTop || 0) : 0) + 'px;right:' + Number(element.type === 'container' ? (props.paddingRight || 0) : 0) + 'px;bottom:' + Number(element.type === 'container' ? (props.paddingBottom || 0) : 0) + 'px;left:' + Number(element.type === 'container' ? (props.paddingLeft || 0) : 0) + 'px;">';
            (element.children || []).forEach(function (child) {
                html += renderElementHtml(child, breakpoint);
            });
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    function renderSelectionOverlay() {
        var bounds = buildSelectionBounds(getSelectionIds(), currentBreakpoint());

        if (!bounds || getSelectionIds().length < 2) {
            return '';
        }

        return '<div class="nbde-selection-box" style="left:' + bounds.x + 'px;top:' + bounds.y + 'px;width:' + bounds.w + 'px;height:' + bounds.h + 'px"></div>';
    }

    function renderFloatingToolbar() {
        var selectionIds = getSelectionIds();
        var primary = getSelectedElement();
        var branch;
        var props;
        var bounds;
        var html = '';

        if (!selectionIds.length) {
            return '';
        }

        bounds = buildSelectionBounds(selectionIds, currentBreakpoint());
        if (!bounds) {
            return '';
        }

        html += '<div class="nbde-toolbar" style="left:' + Math.max(8, bounds.x) + 'px;top:' + Math.max(8, bounds.y - 46) + 'px">';
        html += '<button class="nbde-toolbar__button" type="button" data-action="duplicate-element">Дубль</button>';
        html += '<button class="nbde-toolbar__button" type="button" data-action="delete-element">Удалить</button>';
        html += '<button class="nbde-toolbar__button" type="button" data-action="move-layer-backward">-1</button>';
        html += '<button class="nbde-toolbar__button" type="button" data-action="move-layer-forward">+1</button>';
        html += '<button class="nbde-toolbar__button" type="button" data-action="send-to-back">Назад</button>';
        html += '<button class="nbde-toolbar__button" type="button" data-action="bring-to-front">Вперёд</button>';
        if (selectionIds.length > 1) {
            html += '<button class="nbde-toolbar__button" type="button" data-action="group-selection">Group</button>';
        }
        if (primary && primary.type === 'group') {
            html += '<button class="nbde-toolbar__button" type="button" data-action="ungroup-selection">Ungroup</button>';
        }

        if (primary && selectionIds.length === 1) {
            branch = currentEditableBranch(primary);
            props = branch.props || {};

            if (primary.type === 'text' || primary.type === 'button') {
                html += '<span class="nbde-toolbar__separator"></span>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-text-decrease">A-</button>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-text-increase">A+</button>';
                html += '<label class="nbde-toolbar__color"><input type="color" data-toolbar-color="text" value="' + escapeHtml(getColorInputValue(props.color, primary.type === 'button' ? '#ffffff' : '#0f172a')) + '"></label>';
            }

            if (primary.type === 'shape') {
                html += '<span class="nbde-toolbar__separator"></span>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-radius-decrease">R-</button>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-radius-increase">R+</button>';
                html += '<label class="nbde-toolbar__color"><input type="color" data-toolbar-color="fill" value="' + escapeHtml(getColorInputValue(props.backgroundColor || props.fill, '#f97316')) + '"></label>';
            }
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
        var columnsOpacity = clamp(editorRuntime.columnsGridOpacity == null ? 8 : editorRuntime.columnsGridOpacity, 0, 100);
        var columnsColor = String(editorRuntime.columnsGridColor || '#0f172a');
        var index;

        html += '<div class="nbde-stage" id="nbd-stage-scene" style="--nbde-stage-width:' + Number(stage.width || 1200) + 'px;--nbde-stage-height:' + Number(stage.minHeight || 680) + 'px;">';
        html += '<div class="nbde-stage__surface" style="' + escapeHtml(buildBackgroundStyle(background)) + '">';

        if (editorRuntime.showColumnsGrid) {
            html += '<div class="nbde-stage__columns" style="color:' + escapeHtml(columnsColor) + ';opacity:' + (columnsOpacity / 100) + '">';
            for (index = 0; index < Number(editorRuntime.columnsCount || 12); index++) {
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
            html += '<div class="nbde-stage__empty">Добавьте text или shape из палитры, затем двигайте, масштабируйте и редактируйте их прямо на холсте.</div>';
        } else {
            tree.forEach(function (element) {
                html += renderElementHtml(element, currentBreakpoint());
            });
        }

        html += renderSelectionOverlay();
        html += renderFloatingToolbar();
        html += '</div></div></div>';

        if (nodes.canvasStage) {
            nodes.canvasStage.innerHTML = html;
        }

        if (state.uiState.selectedElementId && nodes.canvasStage) {
            syncInlineTextHeight(
                state.uiState.selectedElementId,
                nodes.canvasStage.querySelector('.nbde-el[data-element-id="' + selectorEscape(state.uiState.selectedElementId) + '"] .nbde-el__body--text'),
                !state.interactionState.drag && !state.interactionState.resize
            );
        }

        updateCanvasMeta();
        focusPendingInlineEditor();
    }

    function renderAll() {
        ensureSelectionState();
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
        if (!element || getSelectionIds().length > 1) {
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

        withEachBreakpoint(element, function (elementBranch, breakpoint) {
            var stageBranch = getPath(state.documentState.contract, 'layout.stage.' + breakpoint, currentStageConfig());
            elementBranch.box.x = Number(stageBranch.paddingX || 24) + offset;
            elementBranch.box.y = Number(stageBranch.paddingY || 24) + offset;
            elementBranch.box.zIndex = elements.length + 1;
        });

        elements.push(element);
        setSelection([element.id], element.id);
        requestInlineFocus(type === 'text' ? element.id : null);
        markDirty();
        renderAll();
    }

    function duplicateSelection() {
        var roots = getRootSelectionIds(getSelectionIds());
        var originalElements = getElements();
        var rootLookup = {};
        var selectedIds = [];
        var originalsById = {};
        var idMap = {};
        var copies = [];

        roots.forEach(function (id) {
            rootLookup[String(id)] = true;
            collectDescendantIds(id).forEach(function (childId) {
                if (selectedIds.indexOf(String(childId)) === -1) {
                    selectedIds.push(String(childId));
                }
            });
        });

        if (!selectedIds.length) {
            return;
        }

        originalElements.forEach(function (element) {
            originalsById[String(element.id)] = element;
            if (selectedIds.indexOf(String(element.id)) === -1) {
                return;
            }

            var copy = clone(element);
            copy.__originId = String(element.id);
            copy.id = nextElementId(copy.type || 'element');
            copy.name = (copy.name || getTypeLabel(copy.type)) + ' копия';
            idMap[String(element.id)] = copy.id;
            copies.push(copy);
        });

        copies.forEach(function (copy) {
            var original = originalsById[copy.__originId];
            copy.parentId = idMap[String(original.parentId || '')] || String(original.parentId || '');

            if (rootLookup[copy.__originId]) {
                withEachBreakpoint(copy, function (branchData) {
                    branchData.box.x = Number(branchData.box.x || 0) + 24;
                    branchData.box.y = Number(branchData.box.y || 0) + 24;
                });
            }

            delete copy.__originId;
        });

        copies.forEach(function (copy) {
            originalElements.push(copy);
        });

        setSelection(roots.map(function (id) {
            return idMap[String(id)];
        }), roots.length ? idMap[String(roots[roots.length - 1])] : null);
        markDirty();
        renderAll();
    }

    function deleteSelection() {
        var ids = [];
        var elements;

        getRootSelectionIds(getSelectionIds()).forEach(function (id) {
            collectDescendantIds(id).forEach(function (descendantId) {
                if (ids.indexOf(String(descendantId)) === -1) {
                    ids.push(String(descendantId));
                }
            });
        });

        if (!ids.length) {
            return;
        }

        elements = getElements().filter(function (element) {
            return ids.indexOf(String(element.id)) === -1;
        });

        state.documentState.contract.content.section.elements = elements;
        clearSelection();
        markDirty();
        renderAll();
    }

    function normalizeZIndices() {
        var ordered = getElements().slice();

        ordered.sort(function (left, right) {
            var leftBox = resolveBranch(left, currentBreakpoint()).box;
            var rightBox = resolveBranch(right, currentBreakpoint()).box;
            return Number(leftBox.zIndex || 0) - Number(rightBox.zIndex || 0);
        });

        ordered.forEach(function (element, index) {
            withEachBreakpoint(element, function (branchData) {
                branchData.box.zIndex = index + 1;
            });
        });
    }

    function shiftSelectionZIndex(delta) {
        var selectedIds = getRootSelectionIds(getSelectionIds());

        if (!selectedIds.length) {
            return;
        }

        selectedIds.forEach(function (id) {
            var element = getElementById(id);
            if (!element) {
                return;
            }

            withEachBreakpoint(element, function (branchData) {
                branchData.box.zIndex = Number(branchData.box.zIndex || 1) + delta;
            });
        });

        normalizeZIndices();
        markDirty();
        renderAll();
    }

    function sendSelectionToEdge(edge) {
        var selectedIds = getRootSelectionIds(getSelectionIds());
        var elements = getElements();
        var max = elements.length + 10;

        if (!selectedIds.length) {
            return;
        }

        selectedIds.forEach(function (id, index) {
            var element = getElementById(id);
            var zIndex = edge === 'front' ? max + index : -selectedIds.length + index;

            if (!element) {
                return;
            }

            withEachBreakpoint(element, function (branchData) {
                branchData.box.zIndex = zIndex;
            });
        });

        normalizeZIndices();
        markDirty();
        renderAll();
    }

    function groupSelection() {
        var selectedIds = getRootSelectionIds(getSelectionIds());
        var selected = selectedIds.map(getElementById).filter(Boolean);
        var elements = getElements();
        var group;
        var parentId;
        var baseBounds = {};

        if (selected.length < 2) {
            state.uiState.lastError = 'Для группировки нужно выбрать минимум два элемента.';
            renderStatus();
            return;
        }

        parentId = String(selected[0].parentId || '');
        if (selected.some(function (element) { return String(element.parentId || '') !== parentId; })) {
            state.uiState.lastError = 'Группировка пока поддерживает только элементы с одним родителем.';
            renderStatus();
            return;
        }

        group = normalizeElement({
            id: nextElementId('group'),
            type: 'group',
            name: 'Группа',
            parentId: parentId
        }, elements.length);

        ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
            var bounds = null;

            selected.forEach(function (element) {
                var branchData = getPath(element, breakpoint, defaultBranch(element.type));
                var box = branchData.box || {};

                if (!bounds) {
                    bounds = {
                        x: Number(box.x || 0),
                        y: Number(box.y || 0),
                        right: Number(box.x || 0) + Number(box.w || 1),
                        bottom: Number(box.y || 0) + Number(box.h || 1),
                        zIndex: Number(box.zIndex || 1)
                    };
                    return;
                }

                bounds.x = Math.min(bounds.x, Number(box.x || 0));
                bounds.y = Math.min(bounds.y, Number(box.y || 0));
                bounds.right = Math.max(bounds.right, Number(box.x || 0) + Number(box.w || 1));
                bounds.bottom = Math.max(bounds.bottom, Number(box.y || 0) + Number(box.h || 1));
                bounds.zIndex = Math.max(bounds.zIndex, Number(box.zIndex || 1));
            });

            bounds = bounds || { x: 0, y: 0, right: 1, bottom: 1, zIndex: elements.length + 1 };
            baseBounds[breakpoint] = bounds;
            group[breakpoint].box.x = bounds.x;
            group[breakpoint].box.y = bounds.y;
            group[breakpoint].box.w = Math.max(1, bounds.right - bounds.x);
            group[breakpoint].box.h = Math.max(1, bounds.bottom - bounds.y);
            group[breakpoint].box.zIndex = bounds.zIndex;
        });

        selected.forEach(function (element) {
            ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
                var branchData = getPath(element, breakpoint, null);
                if (!branchData || !branchData.box) {
                    return;
                }
                branchData.box.x = Number(branchData.box.x || 0) - baseBounds[breakpoint].x;
                branchData.box.y = Number(branchData.box.y || 0) - baseBounds[breakpoint].y;
            });
            element.parentId = group.id;
        });

        elements.push(group);
        setSelection([group.id], group.id);
        markDirty();
        renderAll();
    }

    function ungroupSelection() {
        var group = getSelectedElement();
        var elements = getElements();
        var children;

        if (!group || group.type !== 'group') {
            return;
        }

        children = elements.filter(function (element) {
            return String(element.parentId || '') === String(group.id);
        });

        children.forEach(function (child) {
            ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
                var childBranch = getPath(child, breakpoint, null);
                var groupBranch = getPath(group, breakpoint, null);

                if (!childBranch || !childBranch.box || !groupBranch || !groupBranch.box) {
                    return;
                }

                childBranch.box.x = Number(childBranch.box.x || 0) + Number(groupBranch.box.x || 0);
                childBranch.box.y = Number(childBranch.box.y || 0) + Number(groupBranch.box.y || 0);
            });
            child.parentId = String(group.parentId || '');
        });

        state.documentState.contract.content.section.elements = elements.filter(function (element) {
            return String(element.id) !== String(group.id);
        });
        setSelection(children.map(function (child) { return child.id; }), children.length ? children[children.length - 1].id : null);
        markDirty();
        renderAll();
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
        var size = Math.max(1, Number(step || 8));
        var columns = Math.max(1, Number(count || 12));
        var index;

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
        var host = wrapper ? wrapper.parentElement : null;
        var clickedSelected = isSelected(elementId);
        var nodeIds = clickedSelected ? getRootSelectionIds(getSelectionIds()) : [String(elementId)];
        var startBoxes = {};

        if (!stageScene || !wrapper || !host) {
            return;
        }

        if (!clickedSelected) {
            setSelection([elementId], elementId);
        }

        nodeIds.forEach(function (id) {
            var element = getElementById(id);
            var branchData = element ? currentEditableBranch(element) : null;
            if (!branchData) {
                return;
            }
            startBoxes[id] = {
                x: Number(branchData.box.x || 0),
                y: Number(branchData.box.y || 0),
                w: Number(branchData.box.w || 1),
                h: Number(branchData.box.h || 1)
            };
        });

        event.preventDefault();
        event.stopPropagation();

        state.interactionState.drag = {
            nodeIds: nodeIds,
            primaryId: String(elementId),
            startClientX: event.clientX,
            startClientY: event.clientY,
            startBoxes: startBoxes,
            hostRect: host.getBoundingClientRect(),
            stageRect: stageScene.getBoundingClientRect()
        };

        renderLayersCard();
        renderPropertiesCard();
        renderCanvas();
    }

    function beginResize(elementId, handle, event) {
        var stageScene = document.getElementById('nbd-stage-scene');
        var wrapper = event.target.closest('.nbde-el');
        var host = wrapper ? wrapper.parentElement : null;
        var element = getElementById(elementId);
        var branchData = element ? currentEditableBranch(element) : null;
        var childBoxes = {};

        if (!stageScene || !wrapper || !host || !element || !branchData || getSelectionIds().length !== 1) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (element.type === 'group') {
            getElements().forEach(function (candidate) {
                if (String(candidate.parentId || '') !== String(element.id)) {
                    return;
                }
                childBoxes[candidate.id] = clone(currentEditableBranch(candidate).box || {});
            });
        }

        state.interactionState.resize = {
            elementId: String(elementId),
            handle: String(handle || ''),
            startClientX: event.clientX,
            startClientY: event.clientY,
            startBox: {
                x: Number(branchData.box.x || 0),
                y: Number(branchData.box.y || 0),
                w: Number(branchData.box.w || 1),
                h: Number(branchData.box.h || 1)
            },
            childBoxes: childBoxes,
            hostRect: host.getBoundingClientRect(),
            stageRect: stageScene.getBoundingClientRect()
        };

        renderPropertiesCard();
        renderCanvas();
    }

    function handleDragMove(event) {
        var drag = state.interactionState.drag;
        var editorRuntime = currentEditorRuntime();
        var primaryStart;
        var primaryElement;
        var primaryBranch;
        var hostWidth;
        var hostHeight;
        var nextX;
        var nextY;
        var deltaX;
        var deltaY;
        var xCandidates;
        var yCandidates;

        if (!drag) {
            return;
        }

        primaryStart = drag.startBoxes[drag.primaryId];
        primaryElement = getElementById(drag.primaryId);
        primaryBranch = primaryElement ? currentEditableBranch(primaryElement) : null;

        if (!primaryStart || !primaryBranch) {
            return;
        }

        hostWidth = Math.max(1, Math.round(drag.hostRect.width));
        hostHeight = Math.max(1, Math.round(drag.hostRect.height));
        nextX = primaryStart.x + (event.clientX - drag.startClientX);
        nextY = primaryStart.y + (event.clientY - drag.startClientY);

        if (editorRuntime.snapToGrid) {
            xCandidates = buildCandidates(hostWidth, editorRuntime.gridSize, editorRuntime.columnsCount);
            yCandidates = buildCandidates(hostHeight, editorRuntime.gridSize, 12);
            nextX = snapValue(nextX, xCandidates, Number(editorRuntime.snapThreshold || 6));
            nextY = snapValue(nextY, yCandidates, Number(editorRuntime.snapThreshold || 6));
        }

        deltaX = nextX - primaryStart.x;
        deltaY = nextY - primaryStart.y;

        drag.nodeIds.forEach(function (id) {
            var element = getElementById(id);
            var branchData = element ? currentEditableBranch(element) : null;
            var startBox = drag.startBoxes[id];

            if (!branchData || !startBox) {
                return;
            }

            branchData.box.x = clamp(startBox.x + deltaX, 0, Math.max(0, hostWidth - Number(startBox.w || 1)));
            branchData.box.y = clamp(startBox.y + deltaY, 0, Math.max(0, hostHeight - Number(startBox.h || 1)));
        });

        state.interactionState.guideX = Math.max(0, Math.round((drag.hostRect.left - drag.stageRect.left) + nextX));
        state.interactionState.guideY = Math.max(0, Math.round((drag.hostRect.top - drag.stageRect.top) + nextY));
        markDirty();
        renderPropertiesCard();
        renderCanvas();
    }

    function handleResizeMove(event) {
        var resize = state.interactionState.resize;
        var editorRuntime = currentEditorRuntime();
        var element;
        var branchData;
        var props;
        var minSize;
        var handle;
        var dx;
        var dy;
        var hostWidth;
        var hostHeight;
        var startLeft;
        var startTop;
        var startRight;
        var startBottom;
        var nextLeft;
        var nextTop;
        var nextRight;
        var nextBottom;
        var xCandidates;
        var yCandidates;
        var ratio;
        var widthByDx;
        var widthByDy;
        var nextWidth;
        var nextHeight;
        var scaleX;
        var scaleY;

        if (!resize) {
            return;
        }

        element = getElementById(resize.elementId);
        branchData = element ? currentEditableBranch(element) : null;

        if (!element || !branchData) {
            return;
        }

        props = branchData.props || {};
        minSize = getMinBoxSize(element, props);
        handle = resize.handle;
        dx = event.clientX - resize.startClientX;
        dy = event.clientY - resize.startClientY;
        hostWidth = Math.max(1, Math.round(resize.hostRect.width));
        hostHeight = Math.max(1, Math.round(resize.hostRect.height));
        startLeft = resize.startBox.x;
        startTop = resize.startBox.y;
        startRight = resize.startBox.x + resize.startBox.w;
        startBottom = resize.startBox.y + resize.startBox.h;
        nextLeft = startLeft;
        nextTop = startTop;
        nextRight = startRight;
        nextBottom = startBottom;

        if (isMediaType(element.type) && handle.length === 2) {
            ratio = Math.max(0.01, resize.startBox.w / Math.max(1, resize.startBox.h));
            widthByDx = resize.startBox.w + ((handle.indexOf('w') >= 0 ? -1 : 1) * dx);
            widthByDy = (resize.startBox.h + ((handle.indexOf('n') >= 0 ? -1 : 1) * dy)) * ratio;
            nextWidth = Math.max(minSize.w, Math.abs(widthByDx) >= Math.abs(widthByDy) ? widthByDx : widthByDy);
            nextHeight = Math.max(minSize.h, nextWidth / ratio);

            if (handle.indexOf('w') >= 0) {
                nextLeft = startRight - nextWidth;
            } else {
                nextRight = startLeft + nextWidth;
            }
            if (handle.indexOf('n') >= 0) {
                nextTop = startBottom - nextHeight;
            } else {
                nextBottom = startTop + nextHeight;
            }
        } else {
            if (handle.indexOf('w') >= 0) {
                nextLeft = startLeft + dx;
            }
            if (handle.indexOf('e') >= 0) {
                nextRight = startRight + dx;
            }
            if (handle.indexOf('n') >= 0) {
                nextTop = startTop + dy;
            }
            if (handle.indexOf('s') >= 0) {
                nextBottom = startBottom + dy;
            }
        }

        if (editorRuntime.snapToGrid) {
            xCandidates = buildCandidates(hostWidth, editorRuntime.gridSize, editorRuntime.columnsCount);
            yCandidates = buildCandidates(hostHeight, editorRuntime.gridSize, 12);

            if (handle.indexOf('w') >= 0) {
                nextLeft = snapValue(nextLeft, xCandidates, Number(editorRuntime.snapThreshold || 6));
            }
            if (handle.indexOf('e') >= 0) {
                nextRight = snapValue(nextRight, xCandidates, Number(editorRuntime.snapThreshold || 6));
            }
            if (handle.indexOf('n') >= 0) {
                nextTop = snapValue(nextTop, yCandidates, Number(editorRuntime.snapThreshold || 6));
            }
            if (handle.indexOf('s') >= 0) {
                nextBottom = snapValue(nextBottom, yCandidates, Number(editorRuntime.snapThreshold || 6));
            }
        }

        nextLeft = clamp(nextLeft, 0, hostWidth - minSize.w);
        nextTop = clamp(nextTop, 0, hostHeight - minSize.h);
        nextRight = clamp(nextRight, nextLeft + minSize.w, hostWidth);
        nextBottom = clamp(nextBottom, nextTop + minSize.h, hostHeight);

        branchData.box.x = Math.round(nextLeft);
        branchData.box.y = Math.round(nextTop);
        branchData.box.w = Math.round(nextRight - nextLeft);
        branchData.box.h = Math.round(nextBottom - nextTop);

        if (element.type === 'group' && resize.childBoxes) {
            scaleX = branchData.box.w / Math.max(1, resize.startBox.w);
            scaleY = branchData.box.h / Math.max(1, resize.startBox.h);

            Object.keys(resize.childBoxes).forEach(function (childId) {
                var child = getElementById(childId);
                var childBranch = child ? currentEditableBranch(child) : null;
                var startBox = resize.childBoxes[childId];

                if (!childBranch || !startBox) {
                    return;
                }

                childBranch.box.x = Math.round(Number(startBox.x || 0) * scaleX);
                childBranch.box.y = Math.round(Number(startBox.y || 0) * scaleY);
                childBranch.box.w = Math.max(1, Math.round(Number(startBox.w || 1) * scaleX));
                childBranch.box.h = Math.max(1, Math.round(Number(startBox.h || 1) * scaleY));
            });
        }

        state.interactionState.guideX = handle.indexOf('w') >= 0 ? branchData.box.x : (handle.indexOf('e') >= 0 ? (branchData.box.x + branchData.box.w) : null);
        state.interactionState.guideY = handle.indexOf('n') >= 0 ? branchData.box.y : (handle.indexOf('s') >= 0 ? (branchData.box.y + branchData.box.h) : null);
        markDirty();
        renderPropertiesCard();
        renderCanvas();

        if (element.type === 'text' && nodes.canvasStage) {
            syncInlineTextHeight(element.id, nodes.canvasStage.querySelector('.nbde-el[data-element-id="' + selectorEscape(element.id) + '"] .nbde-el__body--text'), true);
            renderPropertiesCard();
        }
    }

    function finishInteraction() {
        if (!state.interactionState.drag && !state.interactionState.resize) {
            return;
        }

        state.interactionState.drag = null;
        state.interactionState.resize = null;
        clearGuides();
        renderPropertiesCard();
        renderCanvas();
    }

    function applyToolbarAction(action) {
        var element = getSelectedElement();
        var branchData = element ? currentEditableBranch(element) : null;
        var props = branchData ? branchData.props : null;

        if (!element || !branchData || !props) {
            return false;
        }

        if (action === 'toolbar-text-increase') {
            props.fontSize = Number(props.fontSize || (element.type === 'button' ? 16 : 36)) + 2;
            return true;
        }
        if (action === 'toolbar-text-decrease') {
            props.fontSize = Math.max(10, Number(props.fontSize || (element.type === 'button' ? 16 : 36)) - 2);
            return true;
        }
        if (action === 'toolbar-radius-increase') {
            props.borderRadius = Number(props.borderRadius || 0) + 4;
            return true;
        }
        if (action === 'toolbar-radius-decrease') {
            props.borderRadius = Math.max(0, Number(props.borderRadius || 0) - 4);
            return true;
        }

        return false;
    }

    function applyToolbarColor(target) {
        var element = getSelectedElement();
        var branchData = element ? currentEditableBranch(element) : null;
        var props = branchData ? branchData.props : null;
        var role = target.dataset.toolbarColor;

        if (!element || !props || !role) {
            return false;
        }

        if (role === 'text') {
            props.color = target.value;
            return true;
        }

        if (role === 'fill') {
            props.backgroundColor = target.value;
            props.fill = target.value;
            return true;
        }

        return false;
    }

    async function saveContract() {
        var response;
        var payload;

        if (!state.editor.saveUrl || !state.documentState.contract || !state.documentState.block) {
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
            state.palette = getPath(payload, 'palette.items', []).filter(function (item) {
                return item && item.type !== 'group';
            });
            state.pickers = payload.pickers || {};
            state.editor.saveUrl = getPath(payload, 'editor.saveUrl', state.editor.saveUrl);
            state.editor.placeUrl = getPath(payload, 'editor.placeUrl', state.editor.placeUrl);
            state.editor.backUrl = getPath(payload, 'editor.backUrl', state.editor.backUrl);
            state.editor.csrfToken = getPath(payload, 'editor.csrfToken', state.editor.csrfToken);
            state.uiState.activeBreakpoint = getPath(payload, 'ui.activeBreakpoint', state.uiState.activeBreakpoint) || 'desktop';
            state.uiState.isDirty = false;
            state.uiState.lastError = '';
            state.uiState.editingTextId = null;
            state.uiState.pendingFocusTextId = null;
            clearGuides();
            setSelection(getPath(payload, 'ui.selectionIds', []), getPath(payload, 'ui.selectedElementId', null));
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
        var insideCanvas = !!event.target.closest('#nbd-canvas-stage');

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
                if (event.shiftKey) {
                    toggleSelection(actionNode.dataset.elementId || '');
                } else {
                    setSelection([actionNode.dataset.elementId || ''], actionNode.dataset.elementId || '');
                }
                renderAll();
                return;
            }
            if (action === 'duplicate-element') {
                duplicateSelection();
                return;
            }
            if (action === 'delete-element') {
                deleteSelection();
                return;
            }
            if (action === 'group-selection') {
                groupSelection();
                return;
            }
            if (action === 'ungroup-selection') {
                ungroupSelection();
                return;
            }
            if (action === 'move-layer-forward') {
                shiftSelectionZIndex(1);
                return;
            }
            if (action === 'move-layer-backward') {
                shiftSelectionZIndex(-1);
                return;
            }
            if (action === 'bring-to-front') {
                sendSelectionToEdge('front');
                return;
            }
            if (action === 'send-to-back') {
                sendSelectionToEdge('back');
                return;
            }
            if (applyToolbarAction(action)) {
                markDirty();
                renderAll();
                return;
            }
        }

        if (elementNode) {
            if (event.target.closest('[data-inline-edit="text"]')) {
                return;
            }

            if (event.shiftKey) {
                toggleSelection(elementNode.dataset.elementId || '');
            } else {
                setSelection([elementNode.dataset.elementId || ''], elementNode.dataset.elementId || '');
            }
            renderAll();
            return;
        }

        if (insideCanvas) {
            clearSelection();
            renderAll();
        }
    });

    root.addEventListener('dblclick', function (event) {
        var wrapper = event.target.closest('.nbde-el');
        var element;

        if (!wrapper) {
            return;
        }

        element = getElementById(wrapper.dataset.elementId || '');
        if (!element || !isEditableType(element.type)) {
            return;
        }

        setSelection([element.id], element.id);
        requestInlineFocus(element.id);
        renderLayersCard();
        renderPropertiesCard();
        renderCanvas();
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
            syncInlineTextHeight(inlineElement.id, target, inlineElement.type === 'text');
            setSelection([inlineElement.id], inlineElement.id);
            markDirty();
            renderPropertiesCard();
            return;
        }

        if (applyScopedInput(target)) {
            markDirty();
            if (target.dataset.scope === 'element-root' && target.dataset.path === 'name') {
                renderLayersCard();
            }
            renderCanvas();
        }
    });

    root.addEventListener('change', function (event) {
        var target = event.target;

        if (applyToolbarColor(target)) {
            markDirty();
            renderAll();
            return;
        }

        if (applyScopedInput(target)) {
            markDirty();
            if (target.dataset.scope === 'element-root' && target.dataset.path === 'name') {
                renderLayersCard();
            }
            renderCanvas();
        }
    });

    root.addEventListener('focusout', function (event) {
        var target = event.target;
        var wrapper;

        if (target.dataset.inlineEdit !== 'text') {
            return;
        }

        wrapper = target.closest('.nbde-el');
        if (!wrapper) {
            return;
        }

        requestAnimationFrame(function () {
            var active = document.activeElement;
            var stillEditing = active && active.closest && active.closest('.nbde-el');
            if (stillEditing && stillEditing.dataset.elementId === wrapper.dataset.elementId && active.dataset.inlineEdit === 'text') {
                return;
            }

            if (state.uiState.editingTextId === wrapper.dataset.elementId) {
                state.uiState.editingTextId = null;
                renderCanvas();
            }
        });
    }, true);

    root.addEventListener('pointerdown', function (event) {
        var resizeNode = event.target.closest('[data-action="resize-element"]');
        var dragNode = event.target.closest('[data-action="drag-element"]');
        var wrapper = event.target.closest('.nbde-el');

        if (resizeNode && wrapper) {
            beginResize(wrapper.dataset.elementId || '', resizeNode.dataset.handle || '', event);
            return;
        }
        if (dragNode && wrapper) {
            beginDrag(wrapper.dataset.elementId || '', event);
        }
    });

    document.addEventListener('pointermove', handleResizeMove);
    document.addEventListener('pointermove', handleDragMove);
    document.addEventListener('pointerup', finishInteraction);
    document.addEventListener('pointercancel', finishInteraction);

    if (nodes.saveButton) {
        nodes.saveButton.addEventListener('click', function () {
            saveContract();
        });
    }

    window.addEventListener('keydown', function (event) {
        var active = document.activeElement;
        var tagName = active && active.tagName ? active.tagName.toLowerCase() : '';
        var editable = !!(active && active.isContentEditable);

        if ((event.ctrlKey || event.metaKey) && String(event.key || '').toLowerCase() === 's') {
            event.preventDefault();
            saveContract();
            return;
        }

        if ((event.ctrlKey || event.metaKey) && String(event.key || '').toLowerCase() === 'd') {
            if (editable || tagName === 'input' || tagName === 'textarea' || tagName === 'select') {
                return;
            }
            event.preventDefault();
            duplicateSelection();
            return;
        }

        if (String(event.key || '') === 'Delete') {
            if (editable || tagName === 'input' || tagName === 'textarea' || tagName === 'select') {
                return;
            }
            deleteSelection();
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
