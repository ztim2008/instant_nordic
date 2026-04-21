(function () {
    var bootstrap = window.NordicblocksDesignBlockBootstrap || {};
    var GeometryCore = window.NordicblocksDesignBlockGeometryCore;
    var InteractionCore = window.NordicblocksDesignBlockInteractionCore;
    var root = document.getElementById('nbd-editor');

    if (!root) {
        return;
    }

    if (!GeometryCore) {
        console.error('NordicblocksDesignBlockGeometryCore is required for design_block editor');
        return;
    }

    if (!InteractionCore) {
        console.error('NordicblocksDesignBlockInteractionCore is required for design_block editor');
        return;
    }

    var BREAKPOINTS = {
        desktop: { label: 'Компьютер', frameClass: 'nbde-canvas-frame--desktop' },
        tablet: { label: 'Планшет', frameClass: 'nbde-canvas-frame--tablet' },
        mobile: { label: 'Мобильный', frameClass: 'nbde-canvas-frame--mobile' }
    };

    var STAGE_DEFAULTS = {
        desktop: {
            windowWidth: 1440,
            contentWidth: 1320,
            minHeight: 680,
            outerMargin: 60,
            bleedLeft: 60,
            bleedRight: 60,
            columns: 12,
            gutter: 24,
            columnWidth: 88,
            overflowMode: 'auto',
            initialInsertX: 0,
            initialInsertY: 24,
            gridOverlay: {
                color: '#0f172a',
                opacity: 8
            }
        },
        tablet: {
            windowWidth: 768,
            contentWidth: 720,
            minHeight: 560,
            outerMargin: 24,
            bleedLeft: 24,
            bleedRight: 24,
            columns: 8,
            gutter: 24,
            columnWidth: 69,
            overflowMode: 'auto',
            initialInsertX: 0,
            initialInsertY: 20,
            gridOverlay: {
                color: '#0f172a',
                opacity: 8
            }
        },
        mobile: {
            windowWidth: 390,
            contentWidth: 366,
            minHeight: 440,
            outerMargin: 12,
            bleedLeft: 12,
            bleedRight: 12,
            columns: 4,
            gutter: 24,
            columnWidth: 73.5,
            overflowMode: 'auto',
            initialInsertX: 0,
            initialInsertY: 16,
            gridOverlay: {
                color: '#0f172a',
                opacity: 8
            }
        }
    };

    var TYPE_LABELS = {
        text: 'Текст',
        image: 'Фото',
        photo: 'Фото',
        button: 'Кнопка',
        shape: 'Объект',
        object: 'Объект',
        icon: 'Иконка',
        container: 'Контейнер',
        video: 'Видео',
        divider: 'Разделитель',
        svg: 'SVG',
        group: 'Группа'
    };

    var PALETTE_V1_TYPES = {
        text: true,
        button: true,
        object: true,
        photo: true
    };

    var CONTENT_PROP_KEYS = {
        text: true,
        url: true,
        targetBlank: true,
        src: true,
        alt: true,
        poster: true,
        iconClass: true,
        iconPosition: true,
        label: true
    };

    var KNOWN_PROP_KEYS = [
        'opacityPct', 'backgroundColor', 'borderRadius', 'borderWidth', 'borderColor', 'borderStyle', 'boxShadow', 'blur',
        'backdropBlur', 'color', 'fontFamily', 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing', 'textAlign', 'textTransform', 'fill', 'shape', 'objectFit', 'objectPosition',
        'size', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'gap', 'orientation', 'justifyContent', 'text', 'url', 'targetBlank', 'src',
        'alt', 'poster', 'iconClass', 'iconPosition', 'hoverColor', 'hoverBackgroundColor', 'hoverBorderColor', 'label',
        'iconColor', 'hoverIconColor', 'shadowX', 'shadowY', 'shadowBlur', 'shadowSpread', 'shadowColor', 'shadowInset',
        'hoverShadowX', 'hoverShadowY', 'hoverShadowBlur', 'hoverShadowSpread', 'hoverShadowColor', 'hoverShadowInset',
        'backgroundMode', 'gradientFrom', 'gradientTo', 'gradientAngle', 'hoverBackgroundMode', 'hoverGradientFrom', 'hoverGradientTo',
        'hoverScalePct', 'hoverLift', 'hoverShadow', 'transitionDuration'
    ];

    var DEFAULT_FONT_FAMILIES = [
        { value: 'system-ui', label: 'System UI', stack: 'system-ui,-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif', source: 'system' },
        { value: 'montserrat', label: 'Montserrat', stack: '\'Montserrat\',sans-serif', source: 'local' },
        { value: 'unbounded', label: 'Unbounded', stack: '\'Unbounded\',sans-serif', source: 'local' },
        { value: 'play', label: 'Play', stack: '\'Play\',sans-serif', source: 'local' },
        { value: 'philosopher', label: 'Philosopher', stack: '\'Philosopher\',serif', source: 'local' },
        { value: 'playfair-display-sc', label: 'Playfair Display SC', stack: '\'Playfair Display SC\',serif', source: 'local' },
        { value: 'russo-one', label: 'Russo One', stack: '\'Russo One\',sans-serif', source: 'local' }
    ];

    var AUTOSAVE_DELAY_MS = 1200;

    var state = {
        editor: {
            stateUrl: bootstrap.stateUrl || root.dataset.stateUrl || '',
            saveUrl: bootstrap.saveUrl || root.dataset.saveUrl || '',
            backUrl: bootstrap.backUrl || root.dataset.backUrl || '',
            placeUrl: bootstrap.placeUrl || root.dataset.placeUrl || '',
            mediaUploadUrl: bootstrap.mediaUploadUrl || '',
            iconPickerUrl: bootstrap.iconPickerUrl || '',
            iconSpriteUrls: bootstrap.iconSpriteUrls || {},
            csrfToken: bootstrap.csrfToken || ''
        },
        palette: [],
        pickers: {},
        mediaPicker: {
            kind: '',
            scope: '',
            path: '',
            items: [],
            loading: false,
            error: ''
        },
        typography: {
            fontFamilies: normalizeTypographyFamilies(getPath(bootstrap, 'typography.fontFamilies', [])),
            fontFaceCss: String(getPath(bootstrap, 'typography.fontFaceCss', '') || '')
        },
        documentState: {
            block: null,
            contract: null,
            lastSavedAt: null,
            version: 1
        },
        scene: {
            nodes: [],
            layout: createBreakpointStore(),
            props: createBreakpointStore(),
            viewport: {
                zoom: 1,
                offsetX: 0,
                offsetY: 0,
                width: 0,
                height: 0
            }
        },
        uiState: {
            activeBreakpoint: 'desktop',
            selectionIds: [],
            selectedElementId: null,
            addMenuOpen: false,
            rootInsertionMode: false,
            editingTextId: null,
            pendingFocusTextId: null,
            isDirty: false,
            isAutosaveScheduled: false,
            isSaving: false,
            saveMode: 'manual',
            pendingSaveMode: '',
            autosaveTimerId: null,
            changeRevision: 0,
            lastError: ''
        },
        interactionState: {
            guideX: null,
            guideY: null,
            drag: null,
            resize: null,
            rotate: null,
            textIntent: null,
            pan: null,
            pointerCapture: null,
            pointerTelemetry: null,
            numberScrub: null,
            stageResize: null,
            spacePressed: false
        }
    };

    var nodes = {
        titleInput: document.getElementById('nbd-title-input'),
        statusText: document.getElementById('nbd-status-text'),
        canvasMeta: document.getElementById('nbd-canvas-meta'),
        layersSummary: document.getElementById('nbd-layers-summary'),
        propertiesSummary: document.getElementById('nbd-properties-summary'),
        canvasWorkarea: root.querySelector('.nbde-canvas-workarea'),
        frameWrap: document.getElementById('nbd-canvas-frame-wrap'),
        canvasStage: document.getElementById('nbd-canvas-stage'),
        blockCard: document.getElementById('nbd-block-card'),
        stageCard: document.getElementById('nbd-stage-card'),
        sectionCard: document.getElementById('nbd-section-card'),
        layersCard: document.getElementById('nbd-layers-card'),
        propertiesCard: document.getElementById('nbd-properties-card'),
        saveButton: document.getElementById('nbd-save-button')
    };

    var geometryDebugEnabled = !!(bootstrap.devFlags && bootstrap.devFlags.geometryDebug);

    function createBreakpointStore() {
        return { desktop: {}, tablet: {}, mobile: {} };
    }

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

    function roundNumber(value) {
        return Math.round(Number(value || 0));
    }

    function currentBreakpoint() {
        return BREAKPOINTS[state.uiState.activeBreakpoint] ? state.uiState.activeBreakpoint : 'desktop';
    }

    function getBreakpointLabel(key) {
        return (BREAKPOINTS[key] || BREAKPOINTS.desktop).label;
    }

    function normalizeElementType(type) {
        type = String(type || '');

        if (type === 'image') {
            return 'photo';
        }

        if (type === 'shape') {
            return 'object';
        }

        return type;
    }

    function getTypeLabel(type) {
        var index;

        type = normalizeElementType(type);

        for (index = 0; index < state.palette.length; index++) {
            if (state.palette[index].type === type) {
                return state.palette[index].label || TYPE_LABELS[type] || type || 'Элемент';
            }
        }

        return TYPE_LABELS[type] || type || 'Элемент';
    }

    function isPaletteTypeEnabled(type) {
        return !!PALETTE_V1_TYPES[normalizeElementType(type)];
    }

    function isEditableType(type) {
        type = normalizeElementType(type);
        return type === 'text' || type === 'button';
    }

    function isMediaType(type) {
        type = normalizeElementType(type);
        return type === 'photo' || type === 'svg' || type === 'video';
    }

    function normalizeHexColor(value) {
        var normalized = String(value || '').trim();

        if (!/^#[0-9a-f]{3}([0-9a-f]{3})?$/i.test(normalized)) {
            return '';
        }

        return normalized.length === 4
            ? '#' + normalized.charAt(1) + normalized.charAt(1) + normalized.charAt(2) + normalized.charAt(2) + normalized.charAt(3) + normalized.charAt(3)
            : normalized;
    }

    function colorStringToHex(value) {
        var normalized = String(value || '').trim();
        var hexValue = normalizeHexColor(normalized);
        var match;
        var red;
        var green;
        var blue;

        if (hexValue) {
            return hexValue;
        }

        match = normalized.match(/^rgba?\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})(?:\s*,\s*(?:0|1|0?\.[0-9]+))?\s*\)$/i);
        if (!match) {
            return '';
        }

        red = clamp(match[1], 0, 255);
        green = clamp(match[2], 0, 255);
        blue = clamp(match[3], 0, 255);

        return '#'
            + Number(red).toString(16).padStart(2, '0')
            + Number(green).toString(16).padStart(2, '0')
            + Number(blue).toString(16).padStart(2, '0');
    }

    function getColorInputValue(value, fallback) {
        return colorStringToHex(value) || colorStringToHex(fallback) || '#000000';
    }

    function isColorFieldPath(path, kind) {
        if (kind === 'number') {
            return false;
        }

        return /(color|backgroundColor|borderColor|gradientFrom|gradientTo|hoverColor|hoverBackgroundColor|hoverBorderColor|hoverGradientFrom|hoverGradientTo|fill|iconColor|hoverIconColor|shadowColor|hoverShadowColor)$/i.test(String(path || ''));
    }

    function getColorFieldFallback(path) {
        path = String(path || '');

        if (/gradientFrom$/i.test(path)) {
            return '#f8fafc';
        }
        if (/gradientTo$/i.test(path)) {
            return '#e2e8f0';
        }
        if (/hoverGradientFrom$/i.test(path)) {
            return '#ffffff';
        }
        if (/hoverGradientTo$/i.test(path)) {
            return '#dbeafe';
        }
        if (/borderColor$/i.test(path)) {
            return '#cbd5e1';
        }
        if (/hoverBorderColor$/i.test(path)) {
            return '#94a3b8';
        }
        if (/hoverIconColor$/i.test(path)) {
            return '#ffffff';
        }
        if (/iconColor$/i.test(path)) {
            return '#ffffff';
        }
        if (/hoverShadowColor$/i.test(path)) {
            return 'rgba(15,23,42,0.28)';
        }
        if (/shadowColor$/i.test(path)) {
            return 'rgba(15,23,42,0.18)';
        }
        if (/(backgroundColor|fill)$/i.test(path)) {
            return '#f97316';
        }
        if (/hoverBackgroundColor$/i.test(path)) {
            return '#111827';
        }
        if (/hoverColor$/i.test(path)) {
            return '#ffffff';
        }

        return '#0f172a';
    }

    function getPathTail(path) {
        var segments = String(path || '').split('.');
        return segments.length ? segments[segments.length - 1] : '';
    }

    function normalizeScrubNumberValue(path, value) {
        var tail = getPathTail(path);
        var rounded = roundNumber(value);

        if (tail === 'opacityPct') {
            return clamp(rounded, 0, 100);
        }

        if (tail === 'zIndex') {
            return Math.max(1, rounded);
        }

        if (['w', 'h', 'minHeight', 'windowWidth', 'contentWidth', 'columnWidth', 'columns', 'fontSize', 'fontWeight', 'size', 'borderWidth'].indexOf(tail) !== -1) {
            return Math.max(1, rounded);
        }

        if (['borderRadius', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'gap', 'gutter', 'bleedLeft', 'bleedRight', 'outerMargin', 'gridSize', 'snapThreshold', 'lineHeight', 'letterSpacing', 'gradientAngle', 'initialInsertY', 'shadowBlur', 'shadowSpread', 'hoverShadowBlur', 'hoverShadowSpread'].indexOf(tail) !== -1) {
            return Math.max(0, rounded);
        }

        return rounded;
    }

    function defaultBranch(type) {
        type = normalizeElementType(type);
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
            branch.props.fontFamily = 'montserrat';
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
            branch.props.fontFamily = 'montserrat';
            branch.props.fontSize = 16;
            branch.props.fontWeight = 700;
            branch.props.lineHeight = 120;
            branch.props.letterSpacing = 0;
            branch.props.textTransform = 'none';
            branch.props.justifyContent = 'center';
            branch.props.paddingTop = 16;
            branch.props.paddingRight = 28;
            branch.props.paddingBottom = 16;
            branch.props.paddingLeft = 28;
            branch.props.gap = 10;
            branch.props.iconClass = '';
            branch.props.iconPosition = 'start';
            branch.props.iconColor = '';
            branch.props.hoverIconColor = '';
            branch.props.backgroundMode = 'solid';
            branch.props.backgroundColor = '#0f172a';
            branch.props.gradientFrom = '#0f172a';
            branch.props.gradientTo = '#1d4ed8';
            branch.props.gradientAngle = 135;
            branch.props.borderRadius = 999;
            branch.props.shadowX = 0;
            branch.props.shadowY = 0;
            branch.props.shadowBlur = 0;
            branch.props.shadowSpread = 0;
            branch.props.shadowColor = '';
            branch.props.shadowInset = false;
            branch.props.hoverBackgroundMode = 'inherit';
            branch.props.hoverBackgroundColor = '';
            branch.props.hoverGradientFrom = '#111827';
            branch.props.hoverGradientTo = '#2563eb';
            branch.props.hoverColor = '';
            branch.props.hoverBorderColor = '';
            branch.props.hoverShadowX = 0;
            branch.props.hoverShadowY = 0;
            branch.props.hoverShadowBlur = 0;
            branch.props.hoverShadowSpread = 0;
            branch.props.hoverShadowColor = '';
            branch.props.hoverShadowInset = false;
            branch.props.hoverScalePct = 100;
            branch.props.hoverLift = 0;
            branch.props.hoverShadow = '';
            branch.props.transitionDuration = 220;
        } else if (type === 'photo' || type === 'svg') {
            branch.box.w = 420;
            branch.box.h = 260;
            branch.props.src = '';
            branch.props.alt = '';
            branch.props.objectFit = 'cover';
            branch.props.objectPosition = 'center center';
            branch.props.backgroundColor = '';
            branch.props.borderRadius = 24;
        } else if (type === 'video') {
            branch.box.w = 420;
            branch.box.h = 260;
            branch.props.src = '';
            branch.props.poster = '';
            branch.props.objectFit = 'cover';
            branch.props.borderRadius = 24;
        } else if (type === 'object') {
            branch.box.w = 220;
            branch.box.h = 220;
            branch.props.fill = '#f97316';
            branch.props.backgroundColor = '#f97316';
            branch.props.borderRadius = 24;
            branch.props.shape = 'rect';
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
            branch.props.gap = 16;
        } else if (type === 'group') {
            branch.box.w = 360;
            branch.box.h = 220;
            branch.props.label = 'Группа';
        }

        return branch;
    }

    function normalizeLegacyElementShape(element, index) {
        var type = normalizeElementType(element && element.type ? String(element.type) : 'text');
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

    function splitProps(type, props) {
        var baseProps = Object.assign({}, defaultBranch(type).props, props || {});
        var style = {};
        var content = {};

        Object.keys(baseProps).forEach(function (key) {
            if (key === 'rotate') {
                return;
            }

            if (CONTENT_PROP_KEYS[key]) {
                content[key] = baseProps[key];
            } else {
                style[key] = baseProps[key];
            }
        });

        return {
            style: style,
            content: content
        };
    }

    function createSceneNodeFromElement(element, index) {
        var normalized = normalizeLegacyElementShape(element, index);
        var desktopBox = normalized.desktop.box || {};
        var desktopProps = normalized.desktop.props || {};
        var split = splitProps(normalized.type, desktopProps);

        return {
            id: normalized.id,
            type: normalized.type,
            name: normalized.name,
            role: normalized.role,
            parentId: normalized.parentId,
            hidden: normalized.hidden,
            locked: normalized.locked,
            constraints: clone(normalized.constraints || { horizontal: 'left', vertical: 'top' }),
            transform: {
                x: Number(desktopBox.x || 0),
                y: Number(desktopBox.y || 0),
                width: Math.max(1, Number(desktopBox.w || 1)),
                height: Math.max(1, Number(desktopBox.h || 1)),
                rotation: Number(desktopProps.rotate || 0)
            },
            style: split.style,
            content: split.content,
            sharedPropKeys: {}
        };
    }

    function createLayoutEntry(node, box, props) {
        return {
            x: Number(box && box.x != null ? box.x : node.transform.x),
            y: Number(box && box.y != null ? box.y : node.transform.y),
            width: Math.max(1, Number(box && box.w != null ? box.w : node.transform.width)),
            height: Math.max(1, Number(box && box.h != null ? box.h : node.transform.height)),
            rotation: Number(props && props.rotate != null ? props.rotate : node.transform.rotation || 0),
            zIndex: Number(box && box.zIndex != null ? box.zIndex : 1),
            visible: box && box.visible === false ? false : true
        };
    }

    function normalizeContract(contract) {
        var normalized = clone(contract || {});
        var blockTitle = state.documentState.block ? state.documentState.block.title : 'Дизайн-блок';
        var legacyEditorRuntime = getPath(normalized, 'runtime.editor', {});

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
            return normalizeLegacyElementShape(element, index);
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
        ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
            var defaults = clone(STAGE_DEFAULTS[breakpoint] || STAGE_DEFAULTS.desktop);
            var stageBranch = Object.assign({}, defaults, normalized.layout.stage[breakpoint] || {});

            stageBranch.gridOverlay = Object.assign({}, defaults.gridOverlay || {}, stageBranch.gridOverlay || {});
            stageBranch.columns = Number(stageBranch.columns || getPath(stageBranch, 'grid.columns', defaults.columns));
            stageBranch.gutter = Number(stageBranch.gutter || getPath(stageBranch, 'grid.gutter', defaults.gutter));
            stageBranch.columnWidth = Number(stageBranch.columnWidth || defaults.columnWidth);
            stageBranch.overflowMode = String(stageBranch.overflowMode || defaults.overflowMode || 'auto');
            stageBranch.contentWidth = Number(stageBranch.contentWidth || stageBranch.width || ((stageBranch.columns * stageBranch.columnWidth) + (Math.max(0, stageBranch.columns - 1) * stageBranch.gutter)));

            if (stageBranch.outerMargin == null) {
                if (stageBranch.windowWidth != null) {
                    stageBranch.outerMargin = Math.max(0, (Number(stageBranch.windowWidth || 0) - Number(stageBranch.contentWidth || 0)) / 2);
                } else if (stageBranch.grid && stageBranch.grid.bleedX != null) {
                    stageBranch.outerMargin = Number(stageBranch.grid.bleedX || defaults.outerMargin);
                } else {
                    stageBranch.outerMargin = defaults.outerMargin;
                }
            }

            if (stageBranch.windowWidth == null) {
                stageBranch.windowWidth = Number(stageBranch.contentWidth || defaults.contentWidth) + (Number(stageBranch.outerMargin || defaults.outerMargin) * 2);
            }

            if (stageBranch.bleedLeft == null) {
                if (stageBranch.grid && stageBranch.grid.bleedX != null) {
                    stageBranch.bleedLeft = Number(stageBranch.grid.bleedX || defaults.bleedLeft);
                } else if (breakpoint === 'desktop' && legacyEditorRuntime.bleedXDesktop != null) {
                    stageBranch.bleedLeft = Number(legacyEditorRuntime.bleedXDesktop || defaults.bleedLeft);
                } else if (breakpoint === 'tablet' && legacyEditorRuntime.bleedXTablet != null) {
                    stageBranch.bleedLeft = Number(legacyEditorRuntime.bleedXTablet || defaults.bleedLeft);
                } else if (breakpoint === 'mobile' && legacyEditorRuntime.bleedXMobile != null) {
                    stageBranch.bleedLeft = Number(legacyEditorRuntime.bleedXMobile || defaults.bleedLeft);
                } else {
                    stageBranch.bleedLeft = Number(stageBranch.outerMargin || defaults.outerMargin);
                }
            }

            if (stageBranch.bleedRight == null) {
                stageBranch.bleedRight = stageBranch.bleedLeft;
            }

            if (stageBranch.gridOverlay.color == null) {
                stageBranch.gridOverlay.color = legacyEditorRuntime.columnsGridColor || defaults.gridOverlay.color;
            }

            if (stageBranch.gridOverlay.opacity == null) {
                stageBranch.gridOverlay.opacity = legacyEditorRuntime.columnsGridOpacity == null ? defaults.gridOverlay.opacity : legacyEditorRuntime.columnsGridOpacity;
            }

            normalized.layout.stage[breakpoint] = stageBranch;
        });

        normalized.runtime = normalized.runtime || {};
        normalized.runtime.editor = Object.assign({
            snapToGrid: false,
            gridSize: 8,
            snapThreshold: 6,
            showGuides: true,
            showColumnsGrid: true,
            outsideVisibilityMode: 'show',
            columnsGridColor: '#0f172a',
            columnsGridOpacity: 8
        }, normalized.runtime.editor || {});

        return normalized;
    }

    function buildSceneFromContract(contract) {
        var scene = {
            nodes: [],
            layout: createBreakpointStore(),
            props: createBreakpointStore(),
            viewport: {
                zoom: 1,
                offsetX: 0,
                offsetY: 0,
                width: 0,
                height: 0
            }
        };

        getPath(contract, 'content.section.elements', []).forEach(function (element, index) {
            var node = createSceneNodeFromElement(element, index);

            scene.nodes.push(node);

            ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
                var branch = element[breakpoint] || element.desktop || defaultBranch(node.type);
                scene.layout[breakpoint][node.id] = createLayoutEntry(node, branch.box || {}, branch.props || {});
                scene.props[breakpoint][node.id] = clone(branch.props || {});
            });
        });

        return scene;
    }

    function buildSerializableProps(node, breakpoint) {
        var props = {};
        var shared = node.sharedPropKeys || {};
        var layout = ensureLayoutEntry(node, breakpoint);

        Object.assign(props, state.scene.props.desktop[node.id] || {});

        if (breakpoint === 'tablet' || breakpoint === 'mobile') {
            Object.assign(props, state.scene.props.tablet[node.id] || {});
        }

        if (breakpoint === 'mobile') {
            Object.assign(props, state.scene.props.mobile[node.id] || {});
        }

        Object.keys(shared).forEach(function (key) {
            if (!shared[key]) {
                return;
            }

            props[key] = readNodeSharedProp(node, key);
        });

        props.rotate = Number(layout.rotation || 0);
        return props;
    }

    function serializeSceneIntoContract(contract) {
        var next = normalizeContract(contract || {});

        next.content.section.elements = state.scene.nodes.map(function (node) {
            var element = {
                id: node.id,
                type: node.type,
                name: node.name,
                role: node.role,
                parentId: node.parentId,
                hidden: !!node.hidden,
                locked: !!node.locked,
                constraints: clone(node.constraints || { horizontal: 'left', vertical: 'top' })
            };

            ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
                var layout = ensureLayoutEntry(node, breakpoint);

                element[breakpoint] = {
                    box: {
                        x: roundNumber(layout.x),
                        y: roundNumber(layout.y),
                        w: Math.max(1, roundNumber(layout.width)),
                        h: Math.max(1, roundNumber(layout.height)),
                        zIndex: roundNumber(layout.zIndex || 1),
                        visible: layout.visible === false ? false : true
                    },
                    props: buildSerializableProps(node, breakpoint)
                };
            });

            return element;
        });

        return next;
    }

    function getElements() {
        return state.scene.nodes || [];
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

    function getParentElement(elementOrId) {
        var element = typeof elementOrId === 'string' ? getElementById(elementOrId) : elementOrId;

        if (!element || !element.parentId) {
            return null;
        }

        return getElementById(element.parentId);
    }

    function getElementDepth(elementOrId) {
        var depth = 0;
        var parent = getParentElement(elementOrId);

        while (parent) {
            depth += 1;
            parent = getParentElement(parent);
        }

        return depth;
    }

    function buildElementPath(elementId) {
        var path = [];
        var current = getElementById(elementId);

        while (current) {
            path.unshift(current);
            current = getParentElement(current);
        }

        return path;
    }

    function currentStageConfig() {
        return getPath(state.documentState.contract, 'layout.stage.' + currentBreakpoint(), {});
    }

    function currentEditorRuntime() {
        return getPath(state.documentState.contract, 'runtime.editor', {});
    }

    function sanitizeOutsideVisibilityMode(mode) {
        mode = String(mode || 'show').toLowerCase();

        if (mode !== 'show' && mode !== 'hide') {
            return 'auto';
        }

        return mode;
    }

    function getStageDefaults(breakpoint) {
        return clone(STAGE_DEFAULTS[breakpoint] || STAGE_DEFAULTS.desktop);
    }

    function buildStageMetrics(stage, breakpoint) {
        var defaults = getStageDefaults(breakpoint);
        var stageBranch = Object.assign({}, defaults, stage || {});
        var columns = Math.max(1, Number(stageBranch.columns || getPath(stageBranch, 'grid.columns', defaults.columns)));
        var gutter = Math.max(0, Number(stageBranch.gutter || getPath(stageBranch, 'grid.gutter', defaults.gutter)));
        var explicitColumnWidth = Math.max(1, Number(stageBranch.columnWidth || defaults.columnWidth));
        var contentWidth = Math.max(1, Number(stageBranch.contentWidth || stageBranch.width || ((columns * explicitColumnWidth) + (Math.max(0, columns - 1) * gutter))));
        var outerMargin = stageBranch.outerMargin == null
            ? Math.max(0, (Number(stageBranch.windowWidth || (contentWidth + (defaults.outerMargin * 2))) - contentWidth) / 2)
            : Math.max(0, Number(stageBranch.outerMargin || 0));
        var windowWidth = stageBranch.outerMargin == null && stageBranch.windowWidth != null
            ? Math.max(1, Number(stageBranch.windowWidth || 1))
            : Math.max(1, contentWidth + (outerMargin * 2));
        var bleedLeft = Math.max(0, Number(stageBranch.bleedLeft != null ? stageBranch.bleedLeft : getPath(stageBranch, 'grid.bleedX', defaults.bleedLeft)));
        var bleedRight = Math.max(0, Number(stageBranch.bleedRight != null ? stageBranch.bleedRight : getPath(stageBranch, 'grid.bleedX', defaults.bleedRight)));
        var usableWidth = Math.max(columns, contentWidth - (Math.max(0, columns - 1) * gutter));
        var gridOverlay = Object.assign({}, defaults.gridOverlay || {}, stageBranch.gridOverlay || {});

        return {
            width: contentWidth,
            contentWidth: contentWidth,
            height: Math.max(1, Number(stageBranch.minHeight || defaults.minHeight)),
            overflowMode: String(stageBranch.overflowMode || defaults.overflowMode || 'auto'),
            initialInsertX: Number(stageBranch.initialInsertX == null ? defaults.initialInsertX : stageBranch.initialInsertX),
            initialInsertY: Number(stageBranch.initialInsertY == null ? defaults.initialInsertY : stageBranch.initialInsertY),
            columns: columns,
            gutter: gutter,
            bleedLeft: bleedLeft,
            bleedRight: bleedRight,
            originX: outerMargin,
            outerMargin: outerMargin,
            windowWidth: windowWidth,
            columnWidth: usableWidth / columns,
            gridColor: String(gridOverlay.color || '#0f172a'),
            gridOpacity: clamp(gridOverlay.opacity == null ? 8 : gridOverlay.opacity, 0, 100)
        };
    }

    function currentStageMetrics() {
        return buildStageMetrics(currentStageConfig(), currentBreakpoint());
    }

    function readNodeSharedProp(node, key) {
        if (CONTENT_PROP_KEYS[key]) {
            return getPath(node, 'content.' + key, undefined);
        }

        return getPath(node, 'style.' + key, undefined);
    }

    function writeNodeSharedProp(node, key, value) {
        if (CONTENT_PROP_KEYS[key]) {
            node.content = node.content || {};
            node.content[key] = value;
        } else {
            node.style = node.style || {};
            node.style[key] = value;
        }

        node.sharedPropKeys = node.sharedPropKeys || {};
        node.sharedPropKeys[key] = true;
    }

    function composeBreakpointProps(node, breakpoint) {
        var props = {};
        var shared = node.sharedPropKeys || {};

        Object.assign(props, state.scene.props.desktop[node.id] || {});

        if (breakpoint === 'tablet' || breakpoint === 'mobile') {
            Object.assign(props, state.scene.props.tablet[node.id] || {});
        }

        if (breakpoint === 'mobile') {
            Object.assign(props, state.scene.props.mobile[node.id] || {});
        }

        Object.keys(shared).forEach(function (key) {
            if (shared[key]) {
                props[key] = readNodeSharedProp(node, key);
            }
        });

        return props;
    }

    function ensureLayoutEntry(node, breakpoint) {
        if (!state.scene.layout[breakpoint][node.id]) {
            state.scene.layout[breakpoint][node.id] = {
                x: Number(node.transform.x || 0),
                y: Number(node.transform.y || 0),
                width: Math.max(1, Number(node.transform.width || 1)),
                height: Math.max(1, Number(node.transform.height || 1)),
                rotation: Number(node.transform.rotation || 0),
                zIndex: 1,
                visible: true
            };
        }

        return state.scene.layout[breakpoint][node.id];
    }

    function createLayoutBoxBridge(layoutEntry) {
        var mapping = {
            x: 'x',
            y: 'y',
            w: 'width',
            h: 'height',
            zIndex: 'zIndex',
            visible: 'visible',
            rotation: 'rotation'
        };
        var bridge = {};

        Object.keys(mapping).forEach(function (key) {
            Object.defineProperty(bridge, key, {
                enumerable: true,
                get: function () {
                    return layoutEntry[mapping[key]];
                },
                set: function (value) {
                    layoutEntry[mapping[key]] = value;
                }
            });
        });

        return bridge;
    }

    function createPropsBridge(node) {
        var bridge = {};

        KNOWN_PROP_KEYS.forEach(function (key) {
            Object.defineProperty(bridge, key, {
                enumerable: true,
                get: function () {
                    return composeBreakpointProps(node, currentBreakpoint())[key];
                },
                set: function (value) {
                    writeNodeSharedProp(node, key, value);
                }
            });
        });

        return bridge;
    }

    function normalizeTypographyFamilies(items) {
        if (!Array.isArray(items) || !items.length) {
            return DEFAULT_FONT_FAMILIES.slice();
        }

        return items.filter(function (item) {
            return item && item.value;
        }).map(function (item) {
            return {
                value: String(item.value),
                label: String(item.label || item.value),
                stack: String(item.stack || item.value),
                source: String(item.source || 'system')
            };
        });
    }

    function getTypographyFontFamilies() {
        return state.typography.fontFamilies && state.typography.fontFamilies.length ? state.typography.fontFamilies : DEFAULT_FONT_FAMILIES;
    }

    function findTypographyFontFamily(value) {
        var normalized = String(value || 'montserrat');
        var families = getTypographyFontFamilies();
        var index;

        for (index = 0; index < families.length; index += 1) {
            if (families[index].value === normalized) {
                return families[index];
            }
        }

        return families[0] || DEFAULT_FONT_FAMILIES[0];
    }

    function resolveFontFamilyStack(value) {
        return String((findTypographyFontFamily(value) || {}).stack || DEFAULT_FONT_FAMILIES[0].stack);
    }

    function renderFontFamilyField(label, value) {
        return renderSelectField(label, 'element-props', 'fontFamily', value || 'montserrat', getTypographyFontFamilies().map(function (item) {
            return {
                value: item.value,
                label: item.label
            };
        }));
    }

    function ensureTypographyStyles() {
        var styleNode = document.getElementById('nbd-font-face-style');

        if (!styleNode) {
            styleNode = document.createElement('style');
            styleNode.id = 'nbd-font-face-style';
            document.head.appendChild(styleNode);
        }

        styleNode.textContent = String(state.typography.fontFaceCss || '');
    }

    function resolveBranch(element, breakpoint) {
        var layout = ensureLayoutEntry(element, breakpoint);

        return {
            box: {
                x: Number(layout.x || 0),
                y: Number(layout.y || 0),
                w: Math.max(1, Number(layout.width || 1)),
                h: Math.max(1, Number(layout.height || 1)),
                zIndex: Number(layout.zIndex || 1),
                visible: layout.visible === false ? false : true,
                rotation: Number(layout.rotation || 0)
            },
            props: composeBreakpointProps(element, breakpoint)
        };
    }

    function currentEditableBranch(element) {
        return {
            box: createLayoutBoxBridge(ensureLayoutEntry(element, currentBreakpoint())),
            props: createPropsBridge(element)
        };
    }

    function withEachBreakpoint(element, callback) {
        ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
            callback({
                box: createLayoutBoxBridge(ensureLayoutEntry(element, breakpoint)),
                props: createPropsBridge(element)
            }, breakpoint);
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
        return InteractionCore.normalizeSelectionIds(getSelectionSceneNodes(), state.uiState.selectionIds || []);
    }

    function getRootSelectionIds(ids) {
        return InteractionCore.getRootSelectionIds(getSelectionSceneNodes(), ids || getSelectionIds());
    }

    function setRootInsertionMode() {
        state.uiState.selectionIds = [];
        state.uiState.selectedElementId = null;
        state.uiState.rootInsertionMode = true;

        if (state.uiState.editingTextId) {
            state.uiState.editingTextId = null;
            state.uiState.pendingFocusTextId = null;
        }
    }

    function setSelection(ids, primaryId) {
        if (!Array.isArray(ids) || !ids.length) {
            setRootInsertionMode();
            return;
        }

        var selectionState = InteractionCore.createSelectionState(getSelectionSceneNodes(), ids || [], primaryId);

        state.uiState.selectionIds = selectionState.selectionIds;
        state.uiState.selectedElementId = selectionState.primaryId;
        state.uiState.rootInsertionMode = false;

        if (state.uiState.editingTextId && selectionState.selectionIds.indexOf(String(state.uiState.editingTextId)) === -1) {
            state.uiState.editingTextId = null;
            state.uiState.pendingFocusTextId = null;
        }
    }

    function toggleSelection(id) {
        var selectionState = InteractionCore.toggleSelectionState(getSelectionSceneNodes(), getSelectionIds(), id);

        setSelection(selectionState.selectionIds, selectionState.primaryId);
    }

    function clearSelection() {
        setRootInsertionMode();
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

    function focusParentSelection() {
        var selected = getSelectedElement();
        var parent = selected ? getParentElement(selected) : null;

        if (!parent) {
            clearSelection();
            return;
        }

        setSelection([parent.id], parent.id);
    }

    function getInsertionContextElement() {
        var selected = getSelectedElement();

        if (!selected || getSelectionIds().length !== 1) {
            return null;
        }

        if (selected.type === 'container' || selected.type === 'group') {
            return selected;
        }

        return getParentElement(selected);
    }

    function describeInsertionContext() {
        var selection = getSelectionIds();
        var selected = getSelectedElement();
        var context = getInsertionContextElement();

        if (state.uiState.rootInsertionMode || !selection.length) {
            return 'Корень сцены';
        }

        if (selection.length > 1) {
            return 'Корень сцены (мультивыбор)';
        }

        if (context && selected && String(context.id) === String(selected.id)) {
            return 'Внутрь ' + (context.name || getTypeLabel(context.type));
        }

        if (context) {
            return 'Внутрь ' + (context.name || getTypeLabel(context.type));
        }

        return 'Корень сцены';
    }

    function renderSelectionBreadcrumbs() {
        var selected = getSelectedElement();
        var path = selected ? buildElementPath(selected.id) : [];
        var html = '<div class="nbde-crumbs">';

        html += '<button class="nbde-crumb' + ((state.uiState.rootInsertionMode || !path.length) ? ' is-active' : '') + '" type="button" data-action="select-root-context">Сцена</button>';

        path.forEach(function (element) {
            html += '<span class="nbde-crumb-sep">/</span>';
            html += '<button class="nbde-crumb' + (String(state.uiState.selectedElementId || '') === String(element.id) ? ' is-active' : '') + '" type="button" data-action="select-element" data-element-id="' + escapeHtml(element.id) + '">' + escapeHtml(element.name || getTypeLabel(element.type)) + '</button>';
        });

        html += '</div>';
        return html;
    }

    function ensureSelectionState() {
        var ids = getSelectionIds();
        var elements = getElements();

        if (ids.length) {
            setSelection(ids, state.uiState.selectedElementId);
            return;
        }

        if (state.uiState.rootInsertionMode) {
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
        state.uiState.changeRevision += 1;
        state.uiState.lastError = '';
        scheduleAutosave();
        renderStatus();
    }

    function clearAutosaveTimer() {
        if (!state.uiState.autosaveTimerId) {
            return;
        }

        window.clearTimeout(state.uiState.autosaveTimerId);
        state.uiState.autosaveTimerId = null;
    }

    function scheduleAutosave() {
        clearAutosaveTimer();

        if (!state.editor.saveUrl || !state.documentState.contract || !state.documentState.block) {
            state.uiState.isAutosaveScheduled = false;
            return;
        }

        state.uiState.isAutosaveScheduled = true;
        state.uiState.autosaveTimerId = window.setTimeout(function () {
            state.uiState.autosaveTimerId = null;
            state.uiState.isAutosaveScheduled = false;

            if (!state.uiState.isDirty) {
                renderStatus();
                return;
            }

            saveContract({ mode: 'autosave' });
        }, AUTOSAVE_DELAY_MS);
    }

    function buildSaveRequestBody(serializedContract) {
        return JSON.stringify({
            title: state.documentState.block && state.documentState.block.title ? state.documentState.block.title : '',
            contract: serializedContract,
            csrf_token: state.editor.csrfToken
        });
    }

    function triggerKeepaliveSave() {
        var serializedContract;

        if (!state.uiState.isDirty || state.uiState.isSaving || !state.editor.saveUrl || !state.documentState.contract || !state.documentState.block) {
            return;
        }

        clearAutosaveTimer();
        state.uiState.isAutosaveScheduled = false;
        serializedContract = serializeSceneIntoContract(state.documentState.contract);

        fetch(state.editor.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            keepalive: true,
            body: buildSaveRequestBody(serializedContract)
        }).catch(function () {
            return null;
        });
    }

    function clearGuides() {
        state.interactionState.guideX = null;
        state.interactionState.guideY = null;
    }

    function getSelectionSceneNodes() {
        return getElements().map(function (element) {
            return {
                id: element.id,
                parentId: element.parentId || ''
            };
        });
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

    function getAbsoluteWorldBox(element, breakpoint) {
        var branch = resolveBranch(element, breakpoint);
        var box = branch.box || {};
        var absolute = {
            x: Number(box.x || 0),
            y: Number(box.y || 0),
            w: Math.max(1, Number(box.w || 1)),
            h: Math.max(1, Number(box.h || 1)),
            zIndex: Number(box.zIndex || 1),
            rotation: Number(box.rotation || 0)
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
        var candidates = [];

        ids.forEach(function (id) {
            var element = getElementById(id);
            var box;
            var branch;

            if (!element) {
                return;
            }

            box = getAbsoluteWorldBox(element, breakpoint);
            branch = resolveBranch(element, breakpoint);
            candidates.push({
                id: element.id,
                hidden: element.hidden,
                visible: branch.box.visible !== false,
                box: box
            });
        });

        return InteractionCore.buildSelectionBounds(candidates);
    }

    function getAbsoluteZIndex(element, breakpoint) {
        var box = resolveBranch(element, breakpoint).box;
        var parent = element.parentId ? getElementById(element.parentId) : null;

        if (!parent) {
            return Number(box.zIndex || 1);
        }

        return (getAbsoluteZIndex(parent, breakpoint) * 1000) + Number(box.zIndex || 1);
    }

    function getLocalHostSize(element, breakpoint) {
        var parent = element.parentId ? getElementById(element.parentId) : null;
        var stage = currentStageConfig();
        var stageMetrics = buildStageMetrics(stage, breakpoint);
        var parentBranch;
        var parentProps;

        if (!parent) {
            return {
                width: Math.max(1, stageMetrics.width + stageMetrics.bleedRight),
                height: Math.max(1, stageMetrics.height),
                minX: -stageMetrics.bleedLeft,
                minY: 0
            };
        }

        parentBranch = resolveBranch(parent, breakpoint);
        parentProps = parentBranch.props || {};

        if (parent.type === 'container') {
            return {
                width: Math.max(1, Number(parentBranch.box.w || 1) - Number(parentProps.paddingLeft || 0) - Number(parentProps.paddingRight || 0)),
                height: Math.max(1, Number(parentBranch.box.h || 1) - Number(parentProps.paddingTop || 0) - Number(parentProps.paddingBottom || 0))
            };
        }

        return {
            width: Math.max(1, Number(parentBranch.box.w || 1)),
            height: Math.max(1, Number(parentBranch.box.h || 1))
        };
    }

    function hasFreeRootBounds(element) {
        return !!element && !String(element.parentId || '');
    }

    function getInteractionBounds(element, breakpoint) {
        if (hasFreeRootBounds(element)) {
            return null;
        }

        return getLocalHostSize(element, breakpoint);
    }

    function getArtboardBoundsInScene(stageMetrics) {
        return {
            left: -Number(stageMetrics.originX || 0),
            top: 0,
            right: Number(stageMetrics.windowWidth || 1) - Number(stageMetrics.originX || 0),
            bottom: Number(stageMetrics.height || 1)
        };
    }

    function isWorldBoxOutsideArtboard(box, stageMetrics) {
        var bounds;

        if (!box || !stageMetrics) {
            return false;
        }

        bounds = getArtboardBoundsInScene(stageMetrics);

        return Number(box.x || 0) < bounds.left
            || Number(box.y || 0) < bounds.top
            || (Number(box.x || 0) + Number(box.w || 0)) > bounds.right
            || (Number(box.y || 0) + Number(box.h || 0)) > bounds.bottom;
    }

    function getOutsideVisibilityProbeIds() {
        var sourceIds = [];
        var uniqueIds = [];
        var seen = {};

        if (state.interactionState.drag && Array.isArray(state.interactionState.drag.nodeIds)) {
            sourceIds = state.interactionState.drag.nodeIds.slice();
        } else if (state.interactionState.resize && state.interactionState.resize.elementId) {
            sourceIds = [state.interactionState.resize.elementId];
        } else {
            sourceIds = getSelectionIds();
        }

        getRootSelectionIds(sourceIds).forEach(function (id) {
            id = String(id || '');

            if (!id || seen[id]) {
                return;
            }

            seen[id] = true;
            uniqueIds.push(id);
        });

        return uniqueIds;
    }

    function shouldShowOutsideObjects() {
        var mode = sanitizeOutsideVisibilityMode(currentEditorRuntime().outsideVisibilityMode);
        var stageMetrics = currentStageMetrics();
        var breakpoint = currentBreakpoint();

        if (mode === 'show') {
            return true;
        }

        if (mode === 'hide') {
            return false;
        }

        return getOutsideVisibilityProbeIds().some(function (id) {
            var element = getElementById(id);

            return !!element && isWorldBoxOutsideArtboard(getAbsoluteWorldBox(element, breakpoint), stageMetrics);
        });
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
        return InteractionCore.getResizeHandles(element && element.type, {
            orientation: props && props.orientation
        });
    }

    function buildTree(elements, breakpoint) {
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

        function sortByZIndex(source) {
            source.sort(function (left, right) {
                return Number(resolveBranch(left, breakpoint).box.zIndex || 0) - Number(resolveBranch(right, breakpoint).box.zIndex || 0);
            });

            source.forEach(function (item) {
                sortByZIndex(item.children || []);
            });
        }

        sortByZIndex(tree);
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
            nodes.statusText.textContent = state.uiState.saveMode === 'autosave'
                ? 'Автосохраняю изменения...'
                : 'Сохраняю artboard contract...';
            return;
        }

        if (state.uiState.isDirty) {
            nodes.statusText.textContent = state.uiState.isAutosaveScheduled
                ? 'Есть несохранённые изменения. Автосохранение...'
                : 'Есть несохранённые изменения.';
            nodes.statusText.classList.add('is-dirty');
            return;
        }

        if (state.documentState.lastSavedAt) {
            nodes.statusText.textContent = 'Сохранено: ' + state.documentState.lastSavedAt;
            return;
        }

        if (selectionCount > 1) {
            nodes.statusText.textContent = 'Выбрано ' + selectionCount + ' узлов. Новая вставка пойдёт в root scope, пока не останется один primary context.';
            return;
        }

        nodes.statusText.textContent = 'Новая вставка: ' + describeInsertionContext() + '.';
    }

    function updateCanvasWorkareaClass() {
        var stageMetrics;
        var outsideVisibilityMode;
        var outsideVisible;

        if (!nodes.canvasWorkarea) {
            return;
        }

        stageMetrics = currentStageMetrics();
        outsideVisibilityMode = sanitizeOutsideVisibilityMode(currentEditorRuntime().outsideVisibilityMode);
        outsideVisible = shouldShowOutsideObjects();

        nodes.canvasWorkarea.className = 'nbde-canvas-workarea'
            + ' nbde-canvas-workarea--overflow-' + escapeHtml(stageMetrics.overflowMode || 'auto')
            + ' nbde-canvas-workarea--outside-' + escapeHtml(outsideVisibilityMode)
            + (outsideVisible ? ' nbde-canvas-workarea--outside-visible' : ' nbde-canvas-workarea--outside-hidden');
    }

    function updateCanvasMeta() {
        var stage = currentStageConfig();
        var stageMetrics = currentStageMetrics();
        var elements = getElements();
        var selectionCount = getSelectionIds().length;
        var viewport = state.scene.viewport;

        updateCanvasWorkareaClass();

        if (nodes.canvasMeta) {
            nodes.canvasMeta.textContent = getBreakpointLabel(currentBreakpoint())
                + ' • artboard ' + stageMetrics.windowWidth + 'x' + (stage.minHeight || 0) + 'px'
                + ' • grid ' + stageMetrics.contentWidth + 'px'
                + ' • ' + stageMetrics.columns + ' cols'
                + ' • overflow ' + stageMetrics.overflowMode
                + ' • view ' + Math.round(Number(viewport.zoom || 1) * 100) + '% @ ' + roundNumber(viewport.offsetX) + ',' + roundNumber(viewport.offsetY)
                + ' • ' + elements.length + ' узл.'
                + ' • ' + selectionCount + ' выбрано';
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

        updateCanvasWorkareaClass();

        renderStatus();
        updateCanvasMeta();
    }

    function renderNumberField(label, scope, path, value) {
        return '<div class="nbde-field nbde-field--number"><label>' + escapeHtml(label) + '</label><div class="nbde-number-control"><button class="nbde-number-scrub" type="button" data-number-scrub="1" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" aria-label="Изменить ' + escapeHtml(label) + ' движением мыши"></button><input type="number" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="number" value="' + escapeHtml(value == null ? '' : value) + '"></div></div>';
    }

    function renderColorField(label, scope, path, value) {
        var fallback = getColorFieldFallback(path);
        var rawValue = String(value == null ? '' : value).trim();
        var colorValue = getColorInputValue(rawValue, fallback);
        var isEmpty = rawValue === '';

        return '<div class="nbde-field nbde-field--color"><label>' + escapeHtml(label) + '</label><div class="nbde-color-control"><label class="nbde-color-swatch' + (isEmpty ? ' is-empty' : '') + '" aria-label="Выбрать цвет ' + escapeHtml(label) + '"><input type="color" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="string" value="' + escapeHtml(colorValue) + '"><span' + (rawValue ? ' style="background:' + escapeHtml(rawValue) + '"' : '') + '></span></label><button class="nbde-mini-button nbde-color-clear' + (isEmpty ? ' is-empty' : '') + '" type="button" data-color-clear="1" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" aria-label="Очистить цвет ' + escapeHtml(label) + '">x</button><input type="text" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="string" value="' + escapeHtml(value == null ? '' : value) + '"></div></div>';
    }

    function renderField(label, scope, path, value, kind) {
        kind = kind || 'string';

        if (kind === 'number') {
            return renderNumberField(label, scope, path, value);
        }

        if (isColorFieldPath(path, kind)) {
            return renderColorField(label, scope, path, value);
        }

        return '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><input type="text" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="' + escapeHtml(kind) + '" value="' + escapeHtml(value == null ? '' : value) + '"></div>';
    }

    function renderCheckboxField(label, scope, path, checked) {
        return '<div class="nbde-field"><label class="nbde-checkbox"><input type="checkbox" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="boolean" ' + (checked ? 'checked' : '') + '>' + escapeHtml(label) + '</label></div>';
    }

    function renderPickerField(label, scope, path, value, kind, options) {
        var inputType = options && options.inputType ? options.inputType : 'text';
        var pickerLabel = options && options.pickerLabel ? options.pickerLabel : 'Выбрать';
        var clearLabel = options && options.clearLabel ? options.clearLabel : 'Очистить';
        var pickerKind = options && options.pickerKind ? options.pickerKind : 'image';

        return '<div class="nbde-field"><label>' + escapeHtml(label) + '</label><div class="nbde-picker-row"><input type="' + escapeHtml(inputType) + '" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '" data-kind="' + escapeHtml(kind || 'string') + '" value="' + escapeHtml(value == null ? '' : value) + '"><button class="nbde-mini-button nbde-picker-button" type="button" data-picker-action="open" data-picker-kind="' + escapeHtml(pickerKind) + '" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '">' + escapeHtml(pickerLabel) + '</button><button class="nbde-mini-button nbde-picker-button nbde-picker-button--ghost" type="button" data-picker-action="clear" data-scope="' + escapeHtml(scope) + '" data-path="' + escapeHtml(path) + '">' + escapeHtml(clearLabel) + '</button></div></div>';
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

    function buildPhotoPositionOptions() {
        return [
            { value: 'center center', label: 'Центр' },
            { value: 'left top', label: 'Левый верх' },
            { value: 'right top', label: 'Правый верх' },
            { value: 'left bottom', label: 'Левый низ' },
            { value: 'right bottom', label: 'Правый низ' }
        ];
    }

    function buildImageObjectPosition(props) {
        var hasPointPosition = props && (props.objectPositionX != null || props.objectPositionY != null);
        var x;
        var y;

        if (!hasPointPosition) {
            return String((props && props.objectPosition) || 'center center');
        }

        x = Math.max(0, Math.min(100, Number(props.objectPositionX != null ? props.objectPositionX : 50)));
        y = Math.max(0, Math.min(100, Number(props.objectPositionY != null ? props.objectPositionY : 50)));

        return roundNumber(x, 2) + '% ' + roundNumber(y, 2) + '%';
    }

    function buildImageFilterValue(props) {
        var parts = [];
        var brightness = Number(props.filterBrightness != null ? props.filterBrightness : 100);
        var contrast = Number(props.filterContrast != null ? props.filterContrast : 100);
        var saturate = Number(props.filterSaturate != null ? props.filterSaturate : 100);
        var grayscale = Number(props.filterGrayscale != null ? props.filterGrayscale : 0);

        if (brightness !== 100) {
            parts.push('brightness(' + brightness + '%)');
        }
        if (contrast !== 100) {
            parts.push('contrast(' + contrast + '%)');
        }
        if (saturate !== 100) {
            parts.push('saturate(' + saturate + '%)');
        }
        if (grayscale > 0) {
            parts.push('grayscale(' + grayscale + '%)');
        }

        return parts.join(' ');
    }

    function buildImagePreviewStyle(props) {
        var styles = [];
        var filterValue = buildImageFilterValue(props || {});

        styles.push('object-fit:' + String((props && props.objectFit) || 'cover'));
        styles.push('object-position:' + buildImageObjectPosition(props || {}));
        styles.push('border-radius:' + Number((props && props.borderRadius) || 0) + 'px');
        if (filterValue) {
            styles.push('filter:' + filterValue);
        }

        return styles.join(';');
    }

    function shouldPreferOriginalPreview(url) {
        return /\.(png|svg|webp|gif)(?:[?#].*)?$/i.test(String(url || ''));
    }

    function renderAddElementMenu() {
        var html = '<div class="nbde-add-menu">';

        state.palette.forEach(function (item) {
            html += '<button class="nbde-palette-button nbde-palette-item" type="button" data-action="add-element" data-type="' + escapeHtml(item.type) + '">';
            html += '<strong>' + escapeHtml(item.label || getTypeLabel(item.type)) + '</strong>';
            html += '<small>' + escapeHtml(item.description || '') + '</small>';
            html += '</button>';
        });

        html += '</div>';
        return html;
    }

    function renderBlockCard() {
        var selectionIds = getSelectionIds();
        var selected = getSelectedElement();
        var hasParent = selectionIds.length === 1 && !!getParentElement(selected);
        var html = '';

        html += '<div class="nbde-addbar" data-add-menu-root>';
        html += '<button class="nbde-addbar__button" type="button" data-action="toggle-add-menu" aria-expanded="' + (state.uiState.addMenuOpen ? 'true' : 'false') + '"><span class="nbde-addbar__plus">+</span><span>Добавить</span></button>';
        if (state.uiState.addMenuOpen) {
            html += renderAddElementMenu();
        }
        html += '</div>';
        html += '<div class="nbde-context-note"><strong>Новая вставка:</strong> ' + escapeHtml(describeInsertionContext()) + '</div>';
        html += renderSelectionBreadcrumbs();

        if (nodes.blockCard) {
            nodes.blockCard.innerHTML = html;
        }
    }

    function renderStageCard() {
        var stage = currentStageConfig();
        var stageMetrics = currentStageMetrics();
        var editorRuntime = currentEditorRuntime();
        var pointerTelemetry = state.interactionState.pointerTelemetry;
        var pointerCapture = state.interactionState.pointerCapture;
        var viewport = state.scene.viewport;
        var html = '';

        html += '<div class="nbde-action-grid">';
        html += '<button class="nbde-mini-button" type="button" data-action="zoom-out">Zoom -</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="zoom-in">Zoom +</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="zoom-reset">1:1</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="focus-artboard">Фокус блока</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="reveal-selection">Показать выделение</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="apply-stage-height-all">Высоту -> все</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="focus-stage-zone" data-zone="left-bleed">Bleed L</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="focus-stage-zone" data-zone="content">Контент</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="focus-stage-zone" data-zone="right-bleed">Bleed R</button>';
        html += '</div>';
        html += '<div class="nbde-camera-stats">artboard ' + stageMetrics.windowWidth + 'x' + stageMetrics.height + 'px • grid ' + stageMetrics.contentWidth + 'px • columns ' + stageMetrics.columns + ' • gutter ' + stageMetrics.gutter + 'px • bleed ' + stageMetrics.bleedLeft + '/' + stageMetrics.bleedRight + 'px • overflow ' + stageMetrics.overflowMode + '</div>';
        html += '<div class="nbde-camera-stats">editor view ' + Math.round(Number(viewport.zoom || 1) * 100) + '% • offsetX ' + roundNumber(viewport.offsetX) + ' • offsetY ' + roundNumber(viewport.offsetY) + '</div>';
        html += '<div class="nbde-field-grid nbde-field-grid--2">';
        html += renderField('Ширина artboard', 'stage', 'windowWidth', getPath(stage, 'windowWidth', stageMetrics.windowWidth), 'number');
        html += renderField('Ширина grid', 'stage', 'contentWidth', getPath(stage, 'contentWidth', stageMetrics.contentWidth), 'number');
        html += renderField('Высота artboard', 'stage', 'minHeight', stage.minHeight, 'number');
        html += renderSelectField('Overflow artboard', 'stage', 'overflowMode', getPath(stage, 'overflowMode', stageMetrics.overflowMode), [
            { value: 'auto', label: 'Auto' },
            { value: 'hidden', label: 'Hidden' },
            { value: 'visible', label: 'Visible' }
        ]);
        html += renderSelectField('Объекты вне блока', 'runtime-editor', 'outsideVisibilityMode', sanitizeOutsideVisibilityMode(editorRuntime.outsideVisibilityMode), [
            { value: 'auto', label: 'Auto' },
            { value: 'show', label: 'Показывать' },
            { value: 'hide', label: 'Не показывать' }
        ]);
        html += renderField('Поле workspace', 'stage', 'outerMargin', getPath(stage, 'outerMargin', stageMetrics.outerMargin), 'number');
        html += renderField('Bleed слева', 'stage', 'bleedLeft', getPath(stage, 'bleedLeft', stageMetrics.bleedLeft), 'number');
        html += renderField('Bleed справа', 'stage', 'bleedRight', getPath(stage, 'bleedRight', stageMetrics.bleedRight), 'number');
        html += renderField('Колонки', 'stage', 'columns', getPath(stage, 'columns', stageMetrics.columns), 'number');
        html += renderField('Gutter', 'stage', 'gutter', getPath(stage, 'gutter', stageMetrics.gutter), 'number');
        html += renderField('Колонка', 'stage', 'columnWidth', roundNumber(getPath(stage, 'columnWidth', stageMetrics.columnWidth)), 'number');
        html += renderField('Стартовая вставка X', 'stage', 'initialInsertX', getPath(stage, 'initialInsertX', stageMetrics.initialInsertX), 'number');
        html += renderField('Стартовая вставка Y', 'stage', 'initialInsertY', getPath(stage, 'initialInsertY', stageMetrics.initialInsertY), 'number');
        html += renderField('Шаг сетки', 'runtime-editor', 'gridSize', editorRuntime.gridSize, 'number');
        html += renderField('Порог привязки', 'runtime-editor', 'snapThreshold', editorRuntime.snapThreshold, 'number');
        html += renderField('Цвет колонок', 'stage', 'gridOverlay.color', getPath(stage, 'gridOverlay.color', stageMetrics.gridColor), 'string');
        html += renderField('Прозрачность колонок %', 'stage', 'gridOverlay.opacity', getPath(stage, 'gridOverlay.opacity', stageMetrics.gridOpacity), 'number');
        html += '</div>';
        html += '<label class="nbde-checkbox"><input type="checkbox" data-scope="runtime-editor" data-path="snapToGrid" data-kind="boolean" ' + (editorRuntime.snapToGrid ? 'checked' : '') + '>Привязка в world-координатах</label>';
        html += '<label class="nbde-checkbox"><input type="checkbox" data-scope="runtime-editor" data-path="showGuides" data-kind="boolean" ' + (editorRuntime.showGuides ? 'checked' : '') + '>Показывать направляющие</label>';
        html += '<label class="nbde-checkbox"><input type="checkbox" data-scope="runtime-editor" data-path="showColumnsGrid" data-kind="boolean" ' + (editorRuntime.showColumnsGrid ? 'checked' : '') + '>Показывать колонную сетку</label>';

        if (geometryDebugEnabled) {
            html += '<div class="nbde-inline-note">Debug: pointer capture и world/screen telemetry доступны только под dev flag.</div>';
            if (pointerTelemetry) {
                html += '<div class="nbde-camera-stats">screen ' + roundNumber(pointerTelemetry.screenX) + ',' + roundNumber(pointerTelemetry.screenY)
                    + ' • world ' + roundNumber(pointerTelemetry.worldX) + ',' + roundNumber(pointerTelemetry.worldY)
                    + ' • pointer ' + escapeHtml(pointerTelemetry.pointerId)
                    + ' • mode ' + escapeHtml(pointerCapture ? pointerCapture.mode : 'hover') + '</div>';
            } else {
                html += '<div class="nbde-camera-stats">screen -, - • world -, - • mode ' + escapeHtml(pointerCapture ? pointerCapture.mode : 'idle') + '</div>';
            }
        }

        if (nodes.stageCard) {
            nodes.stageCard.innerHTML = html;
        }
    }

    function renderSectionCard() {
        var section = getPath(state.documentState.contract, 'content.section', {});
        var background = getPath(state.documentState.contract, 'design.section.background', {});
        var html = '';

        html += '<div class="nbde-field-grid nbde-field-grid--2">';
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
        html += renderPickerField('Фоновое изображение', 'section-background', 'image', background.image || '', 'string');
        html += '</div>';

        if (nodes.sectionCard) {
            nodes.sectionCard.innerHTML = html;
        }
    }

    function renderLayerTreeItems(source, selectionIds, selectedPathIds, insertionContextId) {
        var html = '';

        source.forEach(function (element) {
            var active = selectionIds.indexOf(String(element.id)) >= 0;
            var ancestor = !active && selectedPathIds.indexOf(String(element.id)) >= 0;
            var isContext = insertionContextId && String(insertionContextId) === String(element.id);
            var children = element.children || [];
            var depth = getElementDepth(element);
            var branch = resolveBranch(element, currentBreakpoint());
            var visible = branch.box.visible !== false;
            var locked = !!element.locked;

            html += '<div class="nbde-layer-row">';
            html += '<div class="nbde-layer-row__main">';
            html += '<button class="nbde-layer-button' + (active ? ' is-active' : '') + (ancestor ? ' is-ancestor' : '') + (isContext ? ' is-context' : '') + (!visible ? ' is-dimmed' : '') + (locked ? ' is-locked' : '') + '" style="--nbde-layer-depth:' + depth + '" type="button" data-action="select-element" data-element-id="' + escapeHtml(element.id) + '">';
            html += '<span class="nbde-layer-meta">';
            html += '<span class="nbde-layer-title"><strong>' + escapeHtml(element.name || getTypeLabel(element.type)) + '</strong><span>' + escapeHtml(getTypeLabel(element.type)) + '</span></span>';
            html += '<span class="nbde-layer-flags">';
            if (isContext) {
                html += '<span class="nbde-layer-pill is-context">insert</span>';
            }
            if (!visible) {
                html += '<span class="nbde-layer-pill">hidden</span>';
            }
            if (locked) {
                html += '<span class="nbde-layer-pill">lock</span>';
            }
            if (children.length) {
                html += '<span class="nbde-layer-pill">' + children.length + ' child</span>';
            }
            html += '</span></span></button>';
            html += '<div class="nbde-layer-actions">';
            html += '<button class="nbde-layer-toggle' + (!visible ? ' is-active' : '') + '" type="button" data-action="toggle-element-visibility" data-element-id="' + escapeHtml(element.id) + '">' + (!visible ? 'Показать' : 'Скрыть') + '</button>';
            html += '<button class="nbde-layer-toggle' + (locked ? ' is-active' : '') + '" type="button" data-action="toggle-element-lock" data-element-id="' + escapeHtml(element.id) + '">' + (locked ? 'Unlock' : 'Lock') + '</button>';
            html += '</div>';
            html += '</div>';

            if (children.length) {
                html += renderLayerTreeItems(children, selectionIds, selectedPathIds, insertionContextId);
            }

            html += '</div>';
        });

        return html;
    }

    function renderLayersCard() {
        var elements = getElements().slice();
        var tree = buildTree(elements, currentBreakpoint());
        var html = '';
        var selectionIds = getSelectionIds();
        var selectedPathIds = buildElementPath(state.uiState.selectedElementId || '').map(function (element) {
            return String(element.id);
        });
        var insertionContext = getInsertionContextElement();

        if (nodes.layersSummary) {
            nodes.layersSummary.textContent = elements.length + ' элементов • ' + describeInsertionContext();
        }

        if (!elements.length) {
            if (nodes.layersCard) {
                nodes.layersCard.innerHTML = '<div class="nbde-card__empty">Добавьте первый элемент.</div>';
            }
            return;
        }
        html += renderSelectionBreadcrumbs();
        html += '<div class="nbde-layer-list nbde-layer-list--tree">';
        html += renderLayerTreeItems(tree, selectionIds, selectedPathIds, insertionContext ? insertionContext.id : '');
        html += '</div>';
        html += '<div class="nbde-action-grid">';
        html += '<button class="nbde-mini-button" type="button" data-action="select-root-context"' + (state.uiState.rootInsertionMode ? ' disabled' : '') + '>В корень</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="select-parent"' + ((selectionIds.length === 1 && getParentElement(getSelectedElement())) ? '' : ' disabled') + '>К parent</button>';
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
        } else if (type === 'photo' || type === 'svg') {
            html += renderPickerField('Файл', 'element-props', 'src', props.src || '', 'string');
            html += renderField('Alt', 'element-props', 'alt', props.alt || '', 'string');
            html += renderSelectField('Object fit', 'element-props', 'objectFit', props.objectFit || 'cover', [
                { value: 'cover', label: 'Cover' },
                { value: 'contain', label: 'Contain' },
                { value: 'fill', label: 'Fill' }
            ]);
            html += renderSelectField('Позиция фото', 'element-props', 'objectPosition', props.objectPosition || 'center center', buildPhotoPositionOptions());
        } else if (type === 'video') {
            html += renderField('Видео файл', 'element-props', 'src', props.src || '', 'string');
            html += renderField('Постер', 'element-props', 'poster', props.poster || '', 'string');
            html += renderSelectField('Object fit', 'element-props', 'objectFit', props.objectFit || 'cover', [
                { value: 'cover', label: 'Cover' },
                { value: 'contain', label: 'Contain' },
                { value: 'fill', label: 'Fill' }
            ]);
        } else if (type === 'object') {
            html += renderField('Заливка', 'element-props', 'backgroundColor', props.backgroundColor || props.fill || '#f97316', 'string');
            html += renderSelectField('Форма', 'element-props', 'shape', props.shape || 'rect', [
                { value: 'rect', label: 'Прямоугольник' },
                { value: 'circle', label: 'Круг' },
                { value: 'line', label: 'Линия' }
            ]);
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
            html += '<div class="nbde-card__empty">Группа объединяет дочерние элементы.</div>';
        }

        html += '</div>';
        return html;
    }

    function renderInspectorSection(title, description, body) {
        var html = '<section class="nbde-inspector-section">';

        html += '<div class="nbde-inspector-section__head">';
        html += '<strong>' + escapeHtml(title) + '</strong>';
        if (description) {
            html += '<span>' + escapeHtml(description) + '</span>';
        }
        html += '</div>';
        html += body && String(body).trim() ? body : '<div class="nbde-card__empty">Для этого блока здесь пока нет дополнительных настроек.</div>';
        html += '</section>';

        return html;
    }

    function renderInspectorSubsection(title, body, description) {
        var html = '<div class="nbde-inspector-subsection">';

        html += '<div class="nbde-inspector-subsection__head">';
        html += '<strong>' + escapeHtml(title) + '</strong>';
        if (description) {
            html += '<span>' + escapeHtml(description) + '</span>';
        }
        html += '</div>';
        html += body && String(body).trim() ? body : '<div class="nbde-card__empty">Нет полей.</div>';
        html += '</div>';

        return html;
    }

    function renderElementContentFields(element, props) {
        var html = '<div class="nbde-field-grid nbde-field-grid--2">';

        html += renderField('Имя элемента', 'element-root', 'name', element.name || '', 'string');
        html += renderField('Роль', 'element-root', 'role', element.role || '', 'string');
        html += '</div>';

        if (element.type === 'text') {
            html += '<div class="nbde-field-grid nbde-field-grid--2">';
            html += renderSelectField('HTML тег', 'element-props', 'tag', props.tag || 'div', [
                { value: 'div', label: 'div' },
                { value: 'h1', label: 'H1' },
                { value: 'h2', label: 'H2' },
                { value: 'h3', label: 'H3' },
                { value: 'h4', label: 'H4' },
                { value: 'p', label: 'p' },
                { value: 'span', label: 'span' }
            ]);
            html += '</div>';
            html += renderTextareaField('Текст', 'element-props', 'text', props.text || '');
        } else if (element.type === 'button') {
            html += renderInspectorSubsection('Текст и ссылка',
                renderTextareaField('Текст кнопки', 'element-props', 'text', props.text || 'Нажмите сюда')
                + '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderField('Ссылка', 'element-props', 'url', props.url || '#', 'string')
                + renderCheckboxField('Открывать в новой вкладке', 'element-props', 'targetBlank', !!props.targetBlank)
                + '</div>'
            );
            html += renderInspectorSubsection('Иконка',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderPickerField('Иконка', 'element-props', 'iconClass', props.iconClass || '', 'string', {
                pickerKind: 'icon',
                pickerLabel: 'Выбрать иконку',
                clearLabel: 'Без иконки'
            })
                + renderSelectField('Позиция иконки', 'element-props', 'iconPosition', props.iconPosition || 'start', [
                { value: 'start', label: 'Слева' },
                { value: 'end', label: 'Справа' }
            ])
                + '</div>',
                'Поддерживаются системные SVG sprite tokens вида brands:telegram.'
            );
        } else if (element.type === 'photo' || element.type === 'svg') {
            html += '<div class="nbde-field-grid nbde-field-grid--2">';
            html += renderPickerField(element.type === 'svg' ? 'SVG файл' : 'Файл', 'element-props', 'src', props.src || '', 'string');
            html += renderField('Alt', 'element-props', 'alt', props.alt || '', 'string');
            html += '</div>';
        } else if (element.type === 'video') {
            html += '<div class="nbde-field-grid nbde-field-grid--2">';
            html += renderField('Видео файл', 'element-props', 'src', props.src || '', 'string');
            html += renderField('Постер', 'element-props', 'poster', props.poster || '', 'string');
            html += '</div>';
        } else if (element.type === 'icon') {
            html += renderField('Класс иконки', 'element-props', 'iconClass', props.iconClass || 'fas fa-star', 'string');
        } else if (element.type === 'divider') {
            html += '';
        } else if (element.type === 'container') {
            html += '';
        } else if (element.type === 'object') {
            html += '';
        }

        return html;
    }

    function renderElementLayoutFields(element, props, box) {
        var html = '<div class="nbde-field-grid nbde-field-grid--2">';

        html += renderField('X', 'element-box', 'x', box.x || 0, 'number');
        html += renderField('Y', 'element-box', 'y', box.y || 0, 'number');
        html += renderField('Ширина', 'element-box', 'w', box.w || 0, 'number');
        html += renderField('Высота', 'element-box', 'h', box.h || 0, 'number');
        html += renderField('Слой', 'element-box', 'zIndex', box.zIndex || 1, 'number');
        html += renderField('Поворот', 'element-box', 'rotation', box.rotation || 0, 'number');

        if (element.type === 'container') {
            html += renderField('Отступ сверху', 'element-props', 'paddingTop', props.paddingTop || 20, 'number');
            html += renderField('Отступ справа', 'element-props', 'paddingRight', props.paddingRight || 20, 'number');
            html += renderField('Отступ снизу', 'element-props', 'paddingBottom', props.paddingBottom || 20, 'number');
            html += renderField('Отступ слева', 'element-props', 'paddingLeft', props.paddingLeft || 20, 'number');
            html += renderField('Внутренний gap', 'element-props', 'gap', props.gap || 16, 'number');
        }

        if (element.type === 'divider') {
            html += renderSelectField('Ориентация', 'element-props', 'orientation', props.orientation || 'horizontal', [
                { value: 'horizontal', label: 'Горизонтально' },
                { value: 'vertical', label: 'Вертикально' }
            ]);
        }

        html += '</div>';
        return html;
    }

    function renderElementStyleFields(element, props) {
        if (element.type === 'button') {
            return renderInspectorSubsection('Каркас',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderField('Непрозрачность %', 'element-props', 'opacityPct', props.opacityPct || 100, 'number')
                + renderField('Скругление', 'element-props', 'borderRadius', props.borderRadius || 0, 'number')
                + renderField('Граница', 'element-props', 'borderWidth', props.borderWidth || 0, 'number')
                + renderField('Цвет границы', 'element-props', 'borderColor', props.borderColor || '', 'string')
                + '</div>'
            )
            + renderInspectorSubsection('Фон',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderSelectField('Тип фона', 'element-props', 'backgroundMode', props.backgroundMode || 'solid', [
                    { value: 'solid', label: 'Сплошной' },
                    { value: 'gradient', label: 'Градиент' }
                ])
                + renderField('Угол градиента', 'element-props', 'gradientAngle', props.gradientAngle != null ? props.gradientAngle : 135, 'number')
                + (((props.backgroundMode || 'solid') === 'gradient')
                    ? renderField('Градиент от', 'element-props', 'gradientFrom', props.gradientFrom || '#0f172a', 'string')
                        + renderField('Градиент к', 'element-props', 'gradientTo', props.gradientTo || '#1d4ed8', 'string')
                    : renderField('Цвет кнопки', 'element-props', 'backgroundColor', props.backgroundColor || '#0f172a', 'string'))
                + '</div>'
            )
            + renderInspectorSubsection('Типографика',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderField('Цвет текста', 'element-props', 'color', props.color || '#ffffff', 'string')
                + renderField('Hover цвет текста', 'element-props', 'hoverColor', props.hoverColor || '', 'string')
                + renderFontFamilyField('Шрифт', props.fontFamily || 'montserrat')
                + renderField('Размер шрифта', 'element-props', 'fontSize', props.fontSize || 16, 'number')
                + renderField('Насыщенность', 'element-props', 'fontWeight', props.fontWeight || 700, 'number')
                + renderField('Межстрочный %', 'element-props', 'lineHeight', props.lineHeight || 120, 'number')
                + renderField('Трекинг', 'element-props', 'letterSpacing', props.letterSpacing || 0, 'number')
                + renderSelectField('Регистр', 'element-props', 'textTransform', props.textTransform || 'none', [
                    { value: 'none', label: 'Обычный' },
                    { value: 'uppercase', label: 'UPPERCASE' },
                    { value: 'lowercase', label: 'lowercase' }
                ])
                + '</div>'
            )
            + renderInspectorSubsection('Иконка',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderField('Цвет иконки', 'element-props', 'iconColor', props.iconColor || '', 'string')
                + renderField('Hover цвет иконки', 'element-props', 'hoverIconColor', props.hoverIconColor || '', 'string')
                + renderField('Gap иконка/текст', 'element-props', 'gap', props.gap || 10, 'number')
                + renderSelectField('Выравнивание контента', 'element-props', 'justifyContent', props.justifyContent || 'center', [
                    { value: 'flex-start', label: 'Слева' },
                    { value: 'center', label: 'По центру' },
                    { value: 'flex-end', label: 'Справа' }
                ])
                + '</div>'
            )
            + renderInspectorSubsection('Тень',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderField('Тень X', 'element-props', 'shadowX', props.shadowX != null ? props.shadowX : 0, 'number')
                + renderField('Тень Y', 'element-props', 'shadowY', props.shadowY != null ? props.shadowY : 0, 'number')
                + renderField('Размытие', 'element-props', 'shadowBlur', props.shadowBlur != null ? props.shadowBlur : 0, 'number')
                + renderField('Spread', 'element-props', 'shadowSpread', props.shadowSpread != null ? props.shadowSpread : 0, 'number')
                + renderField('Цвет тени', 'element-props', 'shadowColor', props.shadowColor || '', 'string')
                + renderCheckboxField('Внутренняя тень', 'element-props', 'shadowInset', !!props.shadowInset)
                + '</div>',
                'Старое raw поле boxShadow остаётся как fallback для уже сохранённых блоков.'
            )
            + renderInspectorSubsection('Hover',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderSelectField('Hover фон', 'element-props', 'hoverBackgroundMode', props.hoverBackgroundMode || 'inherit', [
                    { value: 'inherit', label: 'Как обычный' },
                    { value: 'solid', label: 'Свой цвет' },
                    { value: 'gradient', label: 'Свой градиент' }
                ])
                + (((props.hoverBackgroundMode || 'inherit') === 'gradient')
                    ? renderField('Hover градиент от', 'element-props', 'hoverGradientFrom', props.hoverGradientFrom || props.gradientFrom || '#111827', 'string')
                        + renderField('Hover градиент к', 'element-props', 'hoverGradientTo', props.hoverGradientTo || props.gradientTo || '#2563eb', 'string')
                    : (((props.hoverBackgroundMode || 'inherit') === 'solid')
                        ? renderField('Hover цвет кнопки', 'element-props', 'hoverBackgroundColor', props.hoverBackgroundColor || '', 'string')
                        : ''))
                + renderField('Hover цвет границы', 'element-props', 'hoverBorderColor', props.hoverBorderColor || '', 'string')
                + renderField('Hover тень X', 'element-props', 'hoverShadowX', props.hoverShadowX != null ? props.hoverShadowX : 0, 'number')
                + renderField('Hover тень Y', 'element-props', 'hoverShadowY', props.hoverShadowY != null ? props.hoverShadowY : 0, 'number')
                + renderField('Hover размытие', 'element-props', 'hoverShadowBlur', props.hoverShadowBlur != null ? props.hoverShadowBlur : 0, 'number')
                + renderField('Hover spread', 'element-props', 'hoverShadowSpread', props.hoverShadowSpread != null ? props.hoverShadowSpread : 0, 'number')
                + renderField('Hover цвет тени', 'element-props', 'hoverShadowColor', props.hoverShadowColor || '', 'string')
                + renderCheckboxField('Hover inset', 'element-props', 'hoverShadowInset', !!props.hoverShadowInset)
                + renderField('Hover масштаб %', 'element-props', 'hoverScalePct', props.hoverScalePct != null ? props.hoverScalePct : 100, 'number')
                + renderField('Hover подъём', 'element-props', 'hoverLift', props.hoverLift != null ? props.hoverLift : 0, 'number')
                + renderField('Длительность анимации', 'element-props', 'transitionDuration', props.transitionDuration != null ? props.transitionDuration : 220, 'number')
                + '</div>'
            )
            + renderInspectorSubsection('Отступы',
                '<div class="nbde-field-grid nbde-field-grid--2">'
                + renderField('Padding top', 'element-props', 'paddingTop', props.paddingTop || 16, 'number')
                + renderField('Padding right', 'element-props', 'paddingRight', props.paddingRight || 28, 'number')
                + renderField('Padding bottom', 'element-props', 'paddingBottom', props.paddingBottom || 16, 'number')
                + renderField('Padding left', 'element-props', 'paddingLeft', props.paddingLeft || 28, 'number')
                + '</div>'
            );
        }

        var html = '<div class="nbde-field-grid nbde-field-grid--2">';

        html += renderField('Непрозрачность %', 'element-props', 'opacityPct', props.opacityPct || 100, 'number');
        html += renderField('Скругление', 'element-props', 'borderRadius', props.borderRadius || 0, 'number');
        html += renderField('Граница', 'element-props', 'borderWidth', props.borderWidth || 0, 'number');
        html += renderField('Цвет границы', 'element-props', 'borderColor', props.borderColor || '', 'string');
        html += renderField('Тень', 'element-props', 'boxShadow', props.boxShadow || '', 'string');

        if (element.type === 'text') {
            html += renderField('Цвет текста', 'element-props', 'color', props.color || '#0f172a', 'string');
            html += renderField('Фон', 'element-props', 'backgroundColor', props.backgroundColor || '', 'string');
            html += renderFontFamilyField('Шрифт', props.fontFamily || 'montserrat');
            html += renderField('Размер шрифта', 'element-props', 'fontSize', props.fontSize || 36, 'number');
            html += renderField('Насыщенность', 'element-props', 'fontWeight', props.fontWeight || 800, 'number');
            html += renderField('Межстрочный %', 'element-props', 'lineHeight', props.lineHeight || 120, 'number');
            html += renderField('Трекинг', 'element-props', 'letterSpacing', props.letterSpacing || 0, 'number');
            html += renderSelectField('Выравнивание', 'element-props', 'textAlign', props.textAlign || 'left', [
                { value: 'left', label: 'Слева' },
                { value: 'center', label: 'По центру' },
                { value: 'right', label: 'Справа' }
            ]);
            html += renderSelectField('Регистр', 'element-props', 'textTransform', props.textTransform || 'none', [
                { value: 'none', label: 'Обычный' },
                { value: 'uppercase', label: 'UPPERCASE' },
                { value: 'lowercase', label: 'lowercase' }
            ]);
        } else if (element.type === 'photo' || element.type === 'svg' || element.type === 'video') {
            if (element.type === 'photo' || element.type === 'svg') {
                html += renderField('Подложка', 'element-props', 'backgroundColor', props.backgroundColor || '', 'string');
            }
            html += renderSelectField('Object fit', 'element-props', 'objectFit', props.objectFit || 'cover', [
                { value: 'cover', label: 'Cover' },
                { value: 'contain', label: 'Contain' },
                { value: 'fill', label: 'Fill' },
                { value: 'none', label: 'None' },
                { value: 'scale-down', label: 'Scale down' }
            ]);
            if (element.type === 'photo' || element.type === 'svg') {
                html += renderSelectField('Позиция фото', 'element-props', 'objectPosition', props.objectPosition || 'center center', buildPhotoPositionOptions());
                html += renderField('Позиция X %', 'element-props', 'objectPositionX', props.objectPositionX != null ? props.objectPositionX : 50, 'number');
                html += renderField('Позиция Y %', 'element-props', 'objectPositionY', props.objectPositionY != null ? props.objectPositionY : 50, 'number');
                html += renderField('Brightness %', 'element-props', 'filterBrightness', props.filterBrightness != null ? props.filterBrightness : 100, 'number');
                html += renderField('Contrast %', 'element-props', 'filterContrast', props.filterContrast != null ? props.filterContrast : 100, 'number');
                html += renderField('Saturate %', 'element-props', 'filterSaturate', props.filterSaturate != null ? props.filterSaturate : 100, 'number');
                html += renderField('Grayscale %', 'element-props', 'filterGrayscale', props.filterGrayscale != null ? props.filterGrayscale : 0, 'number');
            }
        } else if (element.type === 'object') {
            html += renderField('Заливка', 'element-props', 'backgroundColor', props.backgroundColor || props.fill || '#f97316', 'string');
            html += renderSelectField('Форма', 'element-props', 'shape', props.shape || 'rect', [
                { value: 'rect', label: 'Прямоугольник' },
                { value: 'circle', label: 'Круг' },
                { value: 'line', label: 'Линия' }
            ]);
        } else if (element.type === 'icon') {
            html += renderField('Цвет иконки', 'element-props', 'color', props.color || '#0f172a', 'string');
            html += renderField('Размер иконки', 'element-props', 'size', props.size || 32, 'number');
        } else if (element.type === 'divider') {
            html += renderField('Цвет разделителя', 'element-props', 'backgroundColor', props.backgroundColor || props.color || '#cbd5e1', 'string');
        } else if (element.type === 'container') {
            html += renderField('Фон контейнера', 'element-props', 'backgroundColor', props.backgroundColor || '', 'string');
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
                nodes.propertiesSummary.textContent = state.uiState.rootInsertionMode ? 'Корень сцены' : 'Ничего не выбрано';
            }
            if (nodes.propertiesCard) {
                nodes.propertiesCard.innerHTML = state.uiState.rootInsertionMode
                    ? renderSelectionBreadcrumbs()
                    : '<div class="nbde-card__empty">Выберите элемент.</div>';
            }
            return;
        }

        if (selection.length > 1) {
            if (nodes.propertiesSummary) {
                nodes.propertiesSummary.textContent = selection.length + ' элементов';
            }
            if (nodes.propertiesCard) {
                nodes.propertiesCard.innerHTML = '<div class="nbde-card__empty">Выбрано несколько элементов.</div>';
            }
            return;
        }

        branch = currentEditableBranch(element);
        props = composeBreakpointProps(element, currentBreakpoint());
        box = branch.box || {};

        if (nodes.propertiesSummary) {
            nodes.propertiesSummary.textContent = (element.name || getTypeLabel(element.type)) + ' • ' + getTypeLabel(element.type);
        }

        html += '<div class="nbde-action-grid">';
        html += '<button class="nbde-mini-button" type="button" data-action="duplicate-element">Дублировать</button>';
        html += '<button class="nbde-danger-button" type="button" data-action="delete-element">Удалить</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="move-layer-backward">Ниже</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="move-layer-forward">Выше</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="toggle-element-visibility" data-element-id="' + escapeHtml(element.id) + '">' + ((branch.box || {}).visible === false ? 'Показать' : 'Скрыть') + '</button>';
        html += '<button class="nbde-mini-button" type="button" data-action="toggle-element-lock" data-element-id="' + escapeHtml(element.id) + '">' + (element.locked ? 'Unlock' : 'Lock') + '</button>';
        html += '</div>';
        html += renderInspectorSection('Контент', 'Содержимое и смысл выбранного объекта.', renderElementContentFields(element, props));
        html += renderInspectorSection('Макет', 'Позиция, размер и порядок в текущей сцене.', renderElementLayoutFields(element, props, box));
        html += renderInspectorSection('Стиль', 'Визуальные свойства выбранного объекта.', renderElementStyleFields(element, props));

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

    function buildCommonBodyStyle(props, box, elementType) {
        var styles = [];
        var backgroundColor = String(props.backgroundColor || '').trim();

        if ((elementType === 'photo' || elementType === 'svg') && props.src && backgroundColor.toLowerCase() === '#e2e8f0') {
            backgroundColor = '';
        }

        if (backgroundColor) {
            styles.push('background:' + backgroundColor);
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
        if (box && box.rotation) {
            styles.push('transform: rotate(' + Number(box.rotation) + 'deg)');
            styles.push('transform-origin: center center');
        }

        return styles.join(';');
    }

    function normalizeButtonIconPosition(value) {
        return String(value || 'start') === 'end' ? 'end' : 'start';
    }

    function normalizeButtonBackgroundMode(value, allowInherit) {
        value = String(value || 'solid');

        if (allowInherit && value === 'inherit') {
            return 'inherit';
        }

        return value === 'gradient' ? 'gradient' : 'solid';
    }

    function parseIconToken(value) {
        var match = String(value || '').trim().match(/^([a-z0-9_-]+):([a-z0-9_-]+)(?::.*)?$/i);

        if (!match) {
            return null;
        }

        return {
            file: match[1],
            name: match[2]
        };
    }

    function renderInlineIconMarkup(value, extraClass) {
        var iconValue = String(value || '').trim();
        var parsed = parseIconToken(iconValue);
        var spriteUrl = parsed ? String(getPath(state, 'editor.iconSpriteUrls.' + parsed.file, '') || '') : '';
        var classes = 'icms-svg-icon' + (extraClass ? ' ' + extraClass : '');

        if (parsed && spriteUrl) {
            return '<svg class="' + escapeHtml(classes) + '" fill="currentColor" aria-hidden="true"><use href="' + escapeHtml(spriteUrl + '#' + parsed.name) + '"></use></svg>';
        }

        if (!iconValue) {
            return '';
        }

        return '<i class="' + escapeHtml(iconValue) + '"></i>';
    }

    function buildStructuredShadowValue(offsetX, offsetY, blur, spread, color, inset, fallbackColor) {
        var normalizedColor = String(color || '').trim() || String(fallbackColor || '').trim() || 'rgba(15,23,42,0.18)';

        return (inset ? 'inset ' : '')
            + Number(offsetX || 0) + 'px '
            + Number(offsetY || 0) + 'px '
            + Math.max(0, Number(blur || 0)) + 'px '
            + Math.max(0, Number(spread || 0)) + 'px '
            + normalizedColor;
    }

    function buildButtonShadowValue(props, hoverMode) {
        var prefix = hoverMode ? 'hoverShadow' : 'shadow';
        var rawValue = String(hoverMode ? (props.hoverShadow || '') : (props.boxShadow || '')).trim();
        var offsetX = Number(props[prefix + 'X'] != null ? props[prefix + 'X'] : 0);
        var offsetY = Number(props[prefix + 'Y'] != null ? props[prefix + 'Y'] : 0);
        var blur = Number(props[prefix + 'Blur'] != null ? props[prefix + 'Blur'] : 0);
        var spread = Number(props[prefix + 'Spread'] != null ? props[prefix + 'Spread'] : 0);
        var color = String(props[prefix + 'Color'] || '').trim();
        var inset = !!props[prefix + 'Inset'];
        var hasStructuredShadow = inset || offsetX !== 0 || offsetY !== 0 || blur !== 0 || spread !== 0 || color !== '';

        if (hasStructuredShadow) {
            return buildStructuredShadowValue(
                offsetX,
                offsetY,
                blur,
                spread,
                color,
                inset,
                hoverMode ? (props.shadowColor || 'rgba(15,23,42,0.18)') : 'rgba(15,23,42,0.18)'
            );
        }

        return rawValue;
    }

    function buildButtonBackgroundValue(props, hoverMode) {
        var mode = hoverMode
            ? normalizeButtonBackgroundMode(props.hoverBackgroundMode, true)
            : normalizeButtonBackgroundMode(props.backgroundMode, false);
        var angle = Math.max(0, Number(props.gradientAngle != null ? props.gradientAngle : 135));
        var fromColor;
        var toColor;

        if (hoverMode && mode === 'inherit') {
            return buildButtonBackgroundValue(props, false);
        }

        if (mode === 'gradient') {
            fromColor = hoverMode ? (props.hoverGradientFrom || props.gradientFrom || props.backgroundColor || '#0f172a') : (props.gradientFrom || props.backgroundColor || '#0f172a');
            toColor = hoverMode ? (props.hoverGradientTo || props.gradientTo || props.backgroundColor || '#1d4ed8') : (props.gradientTo || props.backgroundColor || '#1d4ed8');
            return 'linear-gradient(' + angle + 'deg, ' + String(fromColor) + ', ' + String(toColor) + ')';
        }

        return String(hoverMode ? (props.hoverBackgroundColor || props.backgroundColor || '#0f172a') : (props.backgroundColor || '#0f172a'));
    }

    function buildButtonHoverTransform(props) {
        var scale = Number(props.hoverScalePct != null ? props.hoverScalePct : 100) / 100;
        var lift = Number(props.hoverLift != null ? props.hoverLift : 0);

        return 'translateY(' + (-lift) + 'px) scale(' + roundNumber(scale, 3) + ')';
    }

    function buildButtonPreviewStyle(props, box) {
        var styles = [];
        var paddingTop = Number(props.paddingTop != null ? props.paddingTop : 16);
        var paddingRight = Number(props.paddingRight != null ? props.paddingRight : 28);
        var paddingBottom = Number(props.paddingBottom != null ? props.paddingBottom : 16);
        var paddingLeft = Number(props.paddingLeft != null ? props.paddingLeft : 28);

        styles.push(buildCommonBodyStyle(props, box));
        styles.push('display:flex');
        styles.push('align-items:center');
        styles.push('justify-content:' + String(props.justifyContent || 'center'));
        styles.push('gap:' + Number(props.gap != null ? props.gap : 10) + 'px');
        styles.push('padding:' + paddingTop + 'px ' + paddingRight + 'px ' + paddingBottom + 'px ' + paddingLeft + 'px');
        styles.push('--nbde-button-base-color:' + String(props.color || '#ffffff'));
        styles.push('font-family:' + resolveFontFamilyStack(props.fontFamily || 'montserrat'));
        styles.push('font-size:' + Number(props.fontSize || 16) + 'px');
        styles.push('font-weight:' + Number(props.fontWeight || 700));
        styles.push('line-height:' + (Number(props.lineHeight || 120) / 100));
        styles.push('letter-spacing:' + Number(props.letterSpacing || 0) + 'px');
        styles.push('text-transform:' + String(props.textTransform || 'none'));
        styles.push('--nbde-button-base-bg:' + buildButtonBackgroundValue(props, false));
        styles.push('--nbde-button-base-border:' + String(props.borderColor || 'transparent'));
        styles.push('--nbde-button-icon-color:' + String(props.iconColor || props.color || '#ffffff'));
        styles.push('--nbde-button-hover-icon-color:' + String(props.hoverIconColor || props.hoverColor || props.iconColor || props.color || '#ffffff'));
        styles.push('--nbde-button-base-shadow:' + String(buildButtonShadowValue(props, false) || 'none'));
        styles.push('--nbde-button-hover-bg:' + buildButtonBackgroundValue(props, true));
        styles.push('--nbde-button-hover-color:' + String(props.hoverColor || props.color || '#ffffff'));
        styles.push('--nbde-button-hover-border:' + String(props.hoverBorderColor || props.borderColor || 'transparent'));
        styles.push('--nbde-button-hover-transform:' + buildButtonHoverTransform(props));
        styles.push('--nbde-button-hover-shadow:' + String(buildButtonShadowValue(props, true) || buildButtonShadowValue(props, false) || 'none'));
        styles.push('--nbde-button-transition-duration:' + Math.max(80, Number(props.transitionDuration != null ? props.transitionDuration : 220)) + 'ms');
        styles.push('background:var(--nbde-button-current-bg)');
        styles.push('color:var(--nbde-button-current-color)');
        styles.push('border-color:var(--nbde-button-current-border)');
        styles.push('box-shadow:var(--nbde-button-current-shadow)');

        return styles.filter(Boolean).join(';');
    }

    function renderButtonPreviewContent(props, editing) {
        var label = '<span class="nbde-el__button-label' + (editing ? ' is-editing' : '') + '" contenteditable="' + (editing ? 'true' : 'false') + '" spellcheck="false" data-inline-edit="text">' + textToHtml(props.text || 'Нажмите сюда') + '</span>';
        var iconClass = String(props.iconClass || '').trim();
        var icon = '';

        if (!iconClass) {
            return label;
        }

        icon = renderInlineIconMarkup(iconClass, 'nbde-el__button-icon-svg');

        if (!icon) {
            return label;
        }

        icon = '<span class="nbde-el__button-icon" aria-hidden="true">' + icon + '</span>';
        return normalizeButtonIconPosition(props.iconPosition) === 'end' ? label + icon : icon + label;
    }

    function renderRotateHandle() {
        return '<button class="nbde-el__rotate-handle" type="button" data-action="rotate-element" aria-label="Повернуть элемент"></button>';
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
        var textTag = 'div';
        var selected = isSelected(element.id);
        var primary = String(state.uiState.selectedElementId || '') === String(element.id);
        var editing = String(state.uiState.editingTextId || '') === String(element.id) && isEditableType(element.type);
        var classes = 'nbde-el nbde-el--' + escapeHtml(element.type) + (selected ? ' is-selected' : '') + (primary ? ' is-primary' : '') + (element.hidden ? ' is-hidden' : '') + (element.locked ? ' is-locked' : '');
        var style = [
            'left:' + Number(box.x || 0) + 'px',
            'top:' + Number(box.y || 0) + 'px',
            'width:' + Math.max(1, Number(box.w || 1)) + 'px',
            'height:' + Math.max(1, Number(box.h || 1)) + 'px',
            'z-index:' + Number(box.zIndex || 1),
            'display:' + (box.visible === false ? 'none' : 'block')
        ];
        var html = '<div class="' + classes + '" data-element-id="' + escapeHtml(element.id) + '" data-element-type="' + escapeHtml(element.type) + '" style="' + style.join(';') + '">';

        if (primary && getSelectionIds().length === 1 && !element.locked) {
            html += renderResizeHandles(element, props);
            if (element.type === 'text') {
                html += renderRotateHandle();
            }
        }

        if (element.type === 'text') {
            textTag = ['div', 'h1', 'h2', 'h3', 'h4', 'p', 'span'].indexOf(String(props.tag || 'div')) >= 0 ? String(props.tag || 'div') : 'div';
            html += '<' + textTag + ' class="nbde-el__body nbde-el__body--text' + (editing ? ' is-editing' : '') + '" contenteditable="' + (editing ? 'true' : 'false') + '" spellcheck="false" data-inline-edit="text" style="' + escapeHtml(buildCommonBodyStyle(props, box) + ';margin:0;color:' + String(props.color || '#0f172a') + ';font-family:' + resolveFontFamilyStack(props.fontFamily || 'montserrat') + ';font-size:' + Number(props.fontSize || 36) + 'px;font-weight:' + Number(props.fontWeight || 800) + ';line-height:' + (Number(props.lineHeight || 120) / 100) + ';letter-spacing:' + Number(props.letterSpacing || 0) + 'px;text-align:' + String(props.textAlign || 'left') + ';text-transform:' + String(props.textTransform || 'none')) + '">' + textToHtml(props.text || '') + '</' + textTag + '>';
        } else if (element.type === 'button') {
            html += '<div class="nbde-el__body nbde-el__body--button" style="' + escapeHtml(buildButtonPreviewStyle(props, box)) + '">' + renderButtonPreviewContent(props, editing) + '</div>';
        } else if (element.type === 'photo' || element.type === 'svg') {
            html += '<div class="nbde-el__body nbde-el__body--' + escapeHtml(element.type) + '" style="' + escapeHtml(buildCommonBodyStyle(props, box, element.type)) + '">';
            if (props.src) {
                html += '<img src="' + escapeHtml(props.src) + '" alt="' + escapeHtml(props.alt || '') + '" style="' + escapeHtml(buildImagePreviewStyle(props)) + '">';
            } else {
                html += '<div class="nbde-el__placeholder">Задайте файл в свойствах элемента</div>';
            }
            html += '</div>';
        } else if (element.type === 'video') {
            html += '<div class="nbde-el__body nbde-el__body--video" style="' + escapeHtml(buildCommonBodyStyle(props, box) + ';background:' + String(props.backgroundColor || '#0f172a')) + '"><div class="nbde-el__placeholder">' + escapeHtml(props.src ? 'Видео подключено' : 'Укажите видео файл') + '</div></div>';
        } else if (element.type === 'object') {
            html += '<div class="nbde-el__body" style="' + escapeHtml(buildCommonBodyStyle(props, box) + ';background:' + String((props.shape || 'rect') === 'line' ? 'transparent' : (props.backgroundColor || props.fill || '#f97316')) + ';border-radius:' + Number((props.shape || 'rect') === 'circle' ? 9999 : (props.borderRadius || 0)) + 'px;' + ((props.shape || 'rect') === 'line' ? ('border-top:' + Math.max(1, Number(props.borderWidth || 2)) + 'px solid ' + String(props.borderColor || props.backgroundColor || props.fill || '#f97316') + ';height:0;top:' + Math.round(Number(box.h || 1) / 2) + 'px;') : '')) + '"></div>';
        } else if (element.type === 'icon') {
            html += '<div class="nbde-el__body nbde-el__body--icon" style="' + escapeHtml(buildCommonBodyStyle(props, box) + ';color:' + String(props.color || '#0f172a') + ';font-size:' + Number(props.size || 32) + 'px') + '"><i class="' + escapeHtml(props.iconClass || 'fas fa-star') + '"></i></div>';
        } else if (element.type === 'divider') {
            html += '<div class="nbde-el__body nbde-el__body--divider" style="' + escapeHtml(buildCommonBodyStyle(props, box) + ';background:' + String(props.backgroundColor || props.color || '#cbd5e1')) + '"></div>';
        } else if (element.type === 'container' || element.type === 'group') {
            html += '<div class="nbde-el__body nbde-el__body--group" style="' + escapeHtml(buildCommonBodyStyle(props, box)) + '"></div>';
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

    function projectWorldPoint(viewport, worldX, worldY) {
        var stageMetrics = currentStageMetrics();

        return GeometryCore.projectWorldPoint(viewport, {
            x: worldX + stageMetrics.originX,
            y: worldY
        });
    }

    function screenToWorldPoint(screenX, screenY) {
        var stageMetrics = currentStageMetrics();
        var point = GeometryCore.screenToWorldPoint(state.scene.viewport, {
            x: screenX,
            y: screenY
        });

        return {
            x: point.x - stageMetrics.originX,
            y: point.y
        };
    }

    function projectWorldBounds(bounds) {
        var stageMetrics;

        if (!bounds) {
            return null;
        }

        stageMetrics = currentStageMetrics();

        return GeometryCore.projectWorldRect(state.scene.viewport, {
            x: bounds.x + stageMetrics.originX,
            y: bounds.y,
            w: bounds.w,
            h: bounds.h
        });
    }

    function toCanvasBounds(bounds) {
        var stageMetrics;

        if (!bounds) {
            return null;
        }

        stageMetrics = currentStageMetrics();

        return {
            x: Number(bounds.x || 0) + Number(stageMetrics.originX || 0),
            y: Number(bounds.y || 0),
            w: Math.max(1, Number(bounds.w || 1)),
            h: Math.max(1, Number(bounds.h || 1))
        };
    }

    function getVisibleCanvasRect() {
        var zoom = Math.max(0.01, Number(state.scene.viewport.zoom || 1));
        var stageMetrics = currentStageMetrics();

        return {
            x: Number(state.scene.viewport.offsetX || 0),
            y: Number(state.scene.viewport.offsetY || 0),
            w: Math.max(1, Number(stageMetrics.windowWidth || 1) / zoom),
            h: Math.max(1, Number(stageMetrics.height || 1) / zoom)
        };
    }

    function revealCanvasBounds(canvasBounds, options) {
        var visibleRect;
        var padding;
        var nextOffsetX;
        var nextOffsetY;
        var changed = false;

        if (!canvasBounds) {
            return false;
        }

        visibleRect = getVisibleCanvasRect();
        padding = Math.max(12, Number((options && options.padding) || 48));
        nextOffsetX = Number(state.scene.viewport.offsetX || 0);
        nextOffsetY = Number(state.scene.viewport.offsetY || 0);

        if (options && options.forceCenter) {
            nextOffsetX = (Number(canvasBounds.x || 0) + (Number(canvasBounds.w || 0) / 2)) - (visibleRect.w / 2);
            nextOffsetY = (Number(canvasBounds.y || 0) + (Number(canvasBounds.h || 0) / 2)) - (visibleRect.h / 2);
        } else {
            if ((Number(canvasBounds.w || 0) + (padding * 2)) >= visibleRect.w) {
                nextOffsetX = (Number(canvasBounds.x || 0) + (Number(canvasBounds.w || 0) / 2)) - (visibleRect.w / 2);
            } else if (Number(canvasBounds.x || 0) < (visibleRect.x + padding)) {
                nextOffsetX = Number(canvasBounds.x || 0) - padding;
            } else if ((Number(canvasBounds.x || 0) + Number(canvasBounds.w || 0)) > (visibleRect.x + visibleRect.w - padding)) {
                nextOffsetX = Number(canvasBounds.x || 0) + Number(canvasBounds.w || 0) + padding - visibleRect.w;
            }

            if ((Number(canvasBounds.h || 0) + (padding * 2)) >= visibleRect.h) {
                nextOffsetY = (Number(canvasBounds.y || 0) + (Number(canvasBounds.h || 0) / 2)) - (visibleRect.h / 2);
            } else if (Number(canvasBounds.y || 0) < (visibleRect.y + padding)) {
                nextOffsetY = Number(canvasBounds.y || 0) - padding;
            } else if ((Number(canvasBounds.y || 0) + Number(canvasBounds.h || 0)) > (visibleRect.y + visibleRect.h - padding)) {
                nextOffsetY = Number(canvasBounds.y || 0) + Number(canvasBounds.h || 0) + padding - visibleRect.h;
            }
        }

        nextOffsetX = roundNumber(nextOffsetX);
        nextOffsetY = roundNumber(nextOffsetY);

        if (nextOffsetX !== Number(state.scene.viewport.offsetX || 0)) {
            state.scene.viewport.offsetX = nextOffsetX;
            changed = true;
        }

        if (nextOffsetY !== Number(state.scene.viewport.offsetY || 0)) {
            state.scene.viewport.offsetY = nextOffsetY;
            changed = true;
        }

        return changed;
    }

    function revealSelectionInViewport(forceCenter) {
        return revealCanvasBounds(toCanvasBounds(buildSelectionBounds(getSelectionIds(), currentBreakpoint())), {
            forceCenter: !!forceCenter,
            padding: 48
        });
    }

    function buildSelectionOverlay() {
        var bounds = projectWorldBounds(buildSelectionBounds(getSelectionIds(), currentBreakpoint()));

        if (!bounds || getSelectionIds().length < 2) {
            return '';
        }

        return '<div class="nbde-selection-box" style="left:' + roundNumber(bounds.x) + 'px;top:' + roundNumber(bounds.y) + 'px;width:' + roundNumber(bounds.w) + 'px;height:' + roundNumber(bounds.h) + 'px"></div>';
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

        bounds = projectWorldBounds(buildSelectionBounds(selectionIds, currentBreakpoint()));
        if (!bounds) {
            return '';
        }

        html += '<div class="nbde-toolbar" style="left:' + Math.max(8, roundNumber(bounds.x)) + 'px;top:' + Math.max(8, roundNumber(bounds.y - 46)) + 'px">';
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
            props = composeBreakpointProps(primary, currentBreakpoint());

            if (primary.type === 'text' || primary.type === 'button') {
                html += '<span class="nbde-toolbar__separator"></span>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-text-decrease">A-</button>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-text-increase">A+</button>';
                html += '<label class="nbde-toolbar__color"><input type="color" data-toolbar-color="text" value="' + escapeHtml(getColorInputValue(props.color, primary.type === 'button' ? '#ffffff' : '#0f172a')) + '"></label>';
            }

            if (primary.type === 'object') {
                html += '<span class="nbde-toolbar__separator"></span>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-radius-decrease">R-</button>';
                html += '<button class="nbde-toolbar__button" type="button" data-action="toolbar-radius-increase">R+</button>';
                html += '<label class="nbde-toolbar__color"><input type="color" data-toolbar-color="fill" value="' + escapeHtml(getColorInputValue(props.backgroundColor || props.fill, '#f97316')) + '"></label>';
            }
        }

        html += '</div>';
        return html;
    }

    function buildViewportTransform(viewport) {
        return 'translate(' + roundNumber(-Number(viewport.offsetX || 0) * Number(viewport.zoom || 1)) + 'px,' + roundNumber(-Number(viewport.offsetY || 0) * Number(viewport.zoom || 1)) + 'px) scale(' + Number(viewport.zoom || 1) + ')';
    }

    function collectStageHeights() {
        var heights = {};

        Object.keys(BREAKPOINTS).forEach(function (breakpoint) {
            heights[breakpoint] = buildStageMetrics(getPath(state.documentState.contract, 'layout.stage.' + breakpoint, {}), breakpoint).height;
        });

        return heights;
    }

    function ensureStageBranch(breakpoint) {
        var branch = getPath(state.documentState.contract, 'layout.stage.' + breakpoint, null);

        if (branch && typeof branch === 'object') {
            return branch;
        }

        state.documentState.contract.layout.stage[breakpoint] = clone(STAGE_DEFAULTS[breakpoint] || STAGE_DEFAULTS.desktop);
        return state.documentState.contract.layout.stage[breakpoint];
    }

    function applyViewportScroll(screenDeltaX, screenDeltaY) {
        var zoom = Math.max(0.01, Number(state.scene.viewport.zoom || 1));

        state.scene.viewport.offsetX = Number(state.scene.viewport.offsetX || 0) + (Number(screenDeltaX || 0) / zoom);
        state.scene.viewport.offsetY = Number(state.scene.viewport.offsetY || 0) + (Number(screenDeltaY || 0) / zoom);
        clearGuides();
        renderCanvas();
        renderStageCard();
    }

    function buildCanvasFocusZone(zone) {
        var stageMetrics = currentStageMetrics();
        var leftBleedWidth = Math.max(1, Number(stageMetrics.originX || 0));
        var contentWidth = Math.max(1, Number(stageMetrics.contentWidth || 1));
        var rightBleedStart = Number(stageMetrics.originX || 0) + contentWidth;
        var rightBleedWidth = Math.max(1, Number(stageMetrics.windowWidth || 1) - rightBleedStart);

        if (zone === 'left-bleed') {
            return { x: 0, y: 0, w: leftBleedWidth, h: Math.max(1, Number(stageMetrics.height || 1)) };
        }

        if (zone === 'right-bleed') {
            return { x: rightBleedStart, y: 0, w: rightBleedWidth, h: Math.max(1, Number(stageMetrics.height || 1)) };
        }

        return {
            x: Number(stageMetrics.originX || 0),
            y: 0,
            w: contentWidth,
            h: Math.max(1, Number(stageMetrics.height || 1))
        };
    }

    function focusStageZone(zone) {
        if (!revealCanvasBounds(buildCanvasFocusZone(zone), { forceCenter: true, padding: 24 })) {
            return false;
        }

        clearGuides();
        renderCanvas();
        renderStageCard();
        return true;
    }

    function focusArtboard() {
        var stageMetrics = currentStageMetrics();

        if (!revealCanvasBounds({
            x: 0,
            y: 0,
            w: Math.max(1, Number(stageMetrics.windowWidth || 1)),
            h: Math.max(1, Number(stageMetrics.height || 1))
        }, { forceCenter: true, padding: 24 })) {
            return false;
        }

        clearGuides();
        renderCanvas();
        renderStageCard();
        return true;
    }

    function applyStageHeightToAllBreakpoints() {
        var activeBreakpoint = currentBreakpoint();
        var sourceBranch = ensureStageBranch(activeBreakpoint);
        var sourceHeight = Math.max(240, Number(sourceBranch.minHeight || currentStageMetrics().height));

        Object.keys(BREAKPOINTS).forEach(function (breakpoint) {
            ensureStageBranch(breakpoint).minHeight = sourceHeight;
        });

        markDirty();
        renderCanvas();
        renderStageCard();
        return true;
    }

    function renderStageHeightResizeHandle(stageMetrics) {
        return '<button class="nbde-stage__height-handle' + (state.interactionState.stageResize ? ' is-active' : '') + '" type="button" data-action="resize-stage-height" aria-label="Изменить высоту artboard"><span></span><small>Artboard ' + Math.round(Number(stageMetrics.height || 0)) + 'px</small></button>';
    }

    function renderStageHeightResizeEdge() {
        return '<button class="nbde-stage__height-edge' + (state.interactionState.stageResize ? ' is-active' : '') + '" type="button" data-action="resize-stage-height" aria-label="Потянуть нижний край artboard"></button>';
    }

    function beginStageResize(event) {
        event.preventDefault();
        event.stopPropagation();
        if (event.pointerId != null) {
            acquirePointerCapture(event, 'stage-resize', { breakpoint: currentBreakpoint() });
        }
        state.interactionState.stageResize = {
            activeBreakpoint: currentBreakpoint(),
            startClientY: Number(event.clientY || 0),
            startHeights: collectStageHeights(),
            startZoom: Math.max(0.01, Number(state.scene.viewport.zoom || 1))
        };
        clearGuides();
        renderStageCard();
        renderCanvas();
    }

    function applyStageResizeAtClientY(stageResize, clientY) {
        var activeBreakpoint = stageResize.activeBreakpoint || currentBreakpoint();
        var zoom = Math.max(0.01, Number(stageResize.startZoom || state.scene.viewport.zoom || 1));
        var startHeights = stageResize.startHeights || {};
        var activeStartHeight = Math.max(240, Number(startHeights[activeBreakpoint] || currentStageMetrics().height));
        var nextActiveHeight = Math.max(240, Math.round(activeStartHeight + ((Number(clientY || 0) - Number(stageResize.startClientY || 0)) / zoom)));

        ensureStageBranch(activeBreakpoint).minHeight = nextActiveHeight;

        markDirty();
        renderCanvas();
        renderStageCard();
        return true;
    }

    function renderArtboardGuides(stageMetrics) {
        var rightBleedStart = Number(stageMetrics.originX || 0) + Number(stageMetrics.contentWidth || 0);

        return ''
            + '<div class="nbde-stage__window-frame"></div>'
            + '<div class="nbde-stage__bleed nbde-stage__bleed--left" style="width:' + Math.max(0, Number(stageMetrics.originX || 0)) + 'px"></div>'
            + '<div class="nbde-stage__bleed nbde-stage__bleed--right" style="left:' + rightBleedStart + 'px;width:' + Math.max(0, Number(stageMetrics.windowWidth || 0) - rightBleedStart) + 'px"></div>'
            + '<div class="nbde-stage__grid-outline" style="left:' + Number(stageMetrics.originX || 0) + 'px;width:' + Number(stageMetrics.contentWidth || 0) + 'px"></div>';
    }

    function syncStageDimensions(stageBranch, changedPath) {
        var columns = Math.max(1, Number(stageBranch.columns || 1));
        var gutter = Math.max(0, Number(stageBranch.gutter || 0));
        var outerMargin = Math.max(0, Number(stageBranch.outerMargin || 0));
        var contentWidth = Math.max(1, Number(stageBranch.contentWidth || 1));
        var columnWidth = Math.max(1, Number(stageBranch.columnWidth || 1));

        if (changedPath === 'columns' || changedPath === 'gutter' || changedPath === 'columnWidth') {
            contentWidth = Math.max(1, roundNumber((columns * columnWidth) + (Math.max(0, columns - 1) * gutter)));
            stageBranch.contentWidth = contentWidth;
        } else if (changedPath === 'contentWidth') {
            columnWidth = Math.max(1, (contentWidth - (Math.max(0, columns - 1) * gutter)) / columns);
            stageBranch.columnWidth = roundNumber(columnWidth * 100) / 100;
        }

        if (changedPath === 'windowWidth') {
            stageBranch.outerMargin = Math.max(0, roundNumber((Math.max(1, Number(stageBranch.windowWidth || 1)) - contentWidth) / 2));
        } else {
            stageBranch.windowWidth = Math.max(1, roundNumber(contentWidth + (Math.max(0, Number(stageBranch.outerMargin || outerMargin)) * 2)));
        }
    }

    function toggleElementVisibility(elementId) {
        var element = getElementById(elementId || '');
        var branch = element ? currentEditableBranch(element) : null;

        if (!element || !branch || !branch.box) {
            return;
        }

        branch.box.visible = branch.box.visible === false ? true : false;

        if (branch.box.visible === false && isSelected(element.id)) {
            clearSelection();
        }

        markDirty();
        renderAll();
    }

    function toggleElementLock(elementId) {
        var element = getElementById(elementId || '');

        if (!element) {
            return;
        }

        element.locked = !element.locked;
        markDirty();
        renderAll();
    }

    function renderCanvas() {
        var contract = state.documentState.contract;
        var tree = buildTree(getElements(), currentBreakpoint());
        var stageMetrics = currentStageMetrics();
        var editorRuntime = currentEditorRuntime();
        var background = getPath(contract, 'design.section.background', {});
        var viewport = state.scene.viewport;
        var outsideVisibilityMode = sanitizeOutsideVisibilityMode(editorRuntime.outsideVisibilityMode);
        var outsideVisible = shouldShowOutsideObjects();
        var html = '';
        var columnsOpacity = stageMetrics.gridOpacity;
        var columnsColor = stageMetrics.gridColor;
        var index;

        viewport.width = stageMetrics.windowWidth;
        viewport.height = stageMetrics.height;

        html += '<div class="nbde-stage nbde-stage--overflow-' + escapeHtml(stageMetrics.overflowMode || 'auto')
            + ' nbde-stage--outside-' + escapeHtml(outsideVisibilityMode)
            + (outsideVisible ? ' nbde-stage--outside-visible' : ' nbde-stage--outside-hidden')
            + (state.interactionState.stageResize ? ' is-resizing' : '')
            + '" id="nbd-stage-scene" style="--nbde-stage-width:' + viewport.width + 'px;--nbde-stage-height:' + viewport.height + 'px;--nbde-grid-width:' + stageMetrics.width + 'px;--nbde-grid-left:' + stageMetrics.originX + 'px;--nbde-grid-columns:' + stageMetrics.columns + ';--nbde-grid-gutter:' + stageMetrics.gutter + 'px;">';
        html += '<div class="nbde-stage__viewport' + (state.interactionState.pan ? ' is-panning' : '') + '" id="nbd-stage-viewport">';
        html += '<div class="nbde-stage__world" id="nbd-stage-world" style="transform:' + escapeHtml(buildViewportTransform(viewport)) + '">';
        html += '<div class="nbde-stage__surface" style="' + escapeHtml(buildBackgroundStyle(background)) + '">';
        html += renderArtboardGuides(stageMetrics);

        if (editorRuntime.showColumnsGrid) {
            html += '<div class="nbde-stage__columns" style="color:' + escapeHtml(columnsColor) + ';opacity:' + (columnsOpacity / 100) + '">';
            html += '<div class="nbde-stage__grid-frame"></div>';
            for (index = 0; index < stageMetrics.columns; index++) {
                html += '<span></span>';
            }
            html += '</div>';
        }

        if (editorRuntime.showGuides) {
            html += '<div class="nbde-stage__guides">';
            if (state.interactionState.guideX != null) {
                html += '<div class="nbde-stage__guide nbde-stage__guide--x" style="left:' + Number(state.interactionState.guideX + stageMetrics.originX) + 'px"></div>';
            }
            if (state.interactionState.guideY != null) {
                html += '<div class="nbde-stage__guide nbde-stage__guide--y" style="top:' + Number(state.interactionState.guideY) + 'px"></div>';
            }
            html += '</div>';
        }

        html += '<div class="nbde-stage__scene">';

        if (!tree.length) {
            html += '<div class="nbde-stage__empty">Добавьте node из палитры. Его координаты живут в world, а viewport только проецирует сцену на экран.</div>';
        } else {
            tree.forEach(function (element) {
                html += renderElementHtml(element, currentBreakpoint());
            });
        }

        html += '</div></div></div>';
        html += '<div class="nbde-stage__overlay">' + buildSelectionOverlay() + renderFloatingToolbar() + '</div>';
        html += '<div class="nbde-stage__footer-controls">' + renderStageHeightResizeEdge() + renderStageHeightResizeHandle(stageMetrics) + '</div>';
        html += '</div>';

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

    function getScopedValue(scope, path) {
        var element = getSelectedElement();
        var branch;

        if (!scope || !path) {
            return '';
        }

        if (scope === 'stage') {
            return getPath(state.documentState.contract.layout.stage[currentBreakpoint()], path, '');
        }
        if (scope === 'runtime-editor') {
            return getPath(state.documentState.contract.runtime.editor, path, '');
        }
        if (scope === 'section-content') {
            return getPath(state.documentState.contract.content.section, path, '');
        }
        if (scope === 'section-background') {
            return getPath(state.documentState.contract.design.section.background, path, '');
        }
        if (!element || getSelectionIds().length > 1) {
            return '';
        }

        branch = currentEditableBranch(element);

        if (scope === 'element-root') {
            return getPath(element, path, '');
        }
        if (scope === 'element-box') {
            return getPath(branch.box, path, '');
        }
        if (scope === 'element-props') {
            return getPath(branch.props, path, '');
        }

        return '';
    }

    function applyScopedValue(scope, path, value) {
        var element = getSelectedElement();
        var branch;

        if (!scope || !path) {
            return false;
        }

        if (scope === 'stage') {
            branch = state.documentState.contract.layout.stage[currentBreakpoint()];
            setPath(branch, path, value);
            if (['windowWidth', 'contentWidth', 'outerMargin', 'columnWidth', 'columns', 'gutter'].indexOf(path) !== -1) {
                syncStageDimensions(branch, path);
            }
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

    function refreshAfterScopedMutation(scope, path) {
        markDirty();
        if (scope === 'element-root' && path === 'name') {
            renderLayersCard();
        }
        if (scope === 'stage' || scope === 'runtime-editor') {
            renderStageCard();
        }
        if (scope === 'section-content' || scope === 'section-background') {
            renderSectionCard();
        }
        renderCanvas();
        renderPropertiesCard();
    }

    function setStageCardExpanded(expanded) {
        var section;
        var toggle;

        if (!nodes.stageCard) {
            return;
        }

        section = nodes.stageCard.closest('.nbde-card--accordion');

        if (!section) {
            return;
        }

        toggle = section.querySelector('[data-action="toggle-stage-card"]');
        section.classList.toggle('is-collapsed', !expanded);
        nodes.stageCard.hidden = !expanded;

        if (toggle) {
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function toggleStageCard() {
        var section;

        if (!nodes.stageCard) {
            return;
        }

        section = nodes.stageCard.closest('.nbde-card--accordion');

        if (!section) {
            return;
        }

        setStageCardExpanded(section.classList.contains('is-collapsed'));
    }

    function setSectionCardExpanded(expanded) {
        var section;
        var toggle;

        if (!nodes.sectionCard) {
            return;
        }

        section = nodes.sectionCard.closest('.nbde-card--accordion');

        if (!section) {
            return;
        }

        toggle = section.querySelector('[data-action="toggle-section-card"]');
        section.classList.toggle('is-collapsed', !expanded);
        nodes.sectionCard.hidden = !expanded;

        if (toggle) {
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function toggleSectionCard() {
        var section;

        if (!nodes.sectionCard) {
            return;
        }

        section = nodes.sectionCard.closest('.nbde-card--accordion');

        if (!section) {
            return;
        }

        setSectionCardExpanded(section.classList.contains('is-collapsed'));
    }

    function setLayersCardExpanded(expanded) {
        var section;
        var toggle;

        if (!nodes.layersCard) {
            return;
        }

        section = nodes.layersCard.closest('.nbde-card--accordion');

        if (!section) {
            return;
        }

        toggle = section.querySelector('[data-action="toggle-layers-card"]');
        section.classList.toggle('is-collapsed', !expanded);
        nodes.layersCard.hidden = !expanded;

        if (toggle) {
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    }

    function toggleLayersCard() {
        var section;

        if (!nodes.layersCard) {
            return;
        }

        section = nodes.layersCard.closest('.nbde-card--accordion');

        if (!section) {
            return;
        }

        setLayersCardExpanded(section.classList.contains('is-collapsed'));
    }

    function beginNumberScrub(event, scrubNode) {
        var input;

        if (!scrubNode) {
            return;
        }

        input = scrubNode.parentNode ? scrubNode.parentNode.querySelector('input[data-kind="number"]') : null;

        if (!input) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        state.interactionState.numberScrub = {
            scope: input.dataset.scope || '',
            path: input.dataset.path || '',
            startClientX: Number(event.clientX || 0),
            startValue: Number(input.value || 0),
            lastValue: Number(input.value || 0)
        };
    }

    function applyNumberScrubAtClientX(numberScrub, clientX, fastMode) {
        var multiplier;
        var delta;
        var nextValue;

        if (!numberScrub) {
            return false;
        }

        multiplier = fastMode ? 10 : 1;
        delta = roundNumber((Number(clientX || 0) - Number(numberScrub.startClientX || 0)) / 8) * multiplier;
        nextValue = normalizeScrubNumberValue(numberScrub.path, Number(numberScrub.startValue || 0) + delta);

        if (nextValue === numberScrub.lastValue) {
            return false;
        }

        numberScrub.lastValue = nextValue;

        if (!applyScopedValue(numberScrub.scope, numberScrub.path, nextValue)) {
            return false;
        }

        refreshAfterScopedMutation(numberScrub.scope, numberScrub.path);
        return true;
    }

    function handleNumberScrubMove(event) {
        if (!state.interactionState.numberScrub) {
            return;
        }

        event.preventDefault();
        applyNumberScrubAtClientX(state.interactionState.numberScrub, event.clientX, !!event.shiftKey);
    }

    function closeSystemModal() {
        if (window.icms && icms.modal && typeof icms.modal.close === 'function') {
            icms.modal.close();
        }
    }

    function buildImagePickerCards() {
        var items = Array.isArray(state.mediaPicker.items) ? state.mediaPicker.items : [];
        var html = '';

        if (state.mediaPicker.loading) {
            return '<div class="nbde-image-picker__empty">Загрузка медиабиблиотеки...</div>';
        }

        if (state.mediaPicker.error) {
            return '<div class="nbde-image-picker__empty nbde-image-picker__empty--error">' + escapeHtml(state.mediaPicker.error) + '</div>';
        }

        if (!items.length) {
            return '<div class="nbde-image-picker__empty">В медиабиблиотеке пока нет изображений.</div>';
        }

        items.forEach(function (item, index) {
            var media = item && item.media ? item.media : {};
            var originalUrl = media.original || item.preview_fallback_url || '';
            var previewUrl = shouldPreferOriginalPreview(originalUrl)
                ? originalUrl
                : (item.preview_url || item.preview_fallback_url || media.display || media.original || '');
            var title = item.title || item.alt || media.alt || media.original_path || ('Изображение ' + String(index + 1));
            var subtitle = media.original_path || item.original_path || '';

            html += '<button class="nbde-image-picker__card" type="button" data-media-picker-action="select" data-media-index="' + String(index) + '">';
            html += '<span class="nbde-image-picker__preview">';
            html += previewUrl
                ? '<img src="' + escapeHtml(previewUrl) + '" alt="' + escapeHtml(title) + '">'
                : '<span class="nbde-image-picker__placeholder">Нет preview</span>';
            html += '</span>';
            html += '<span class="nbde-image-picker__meta">';
            html += '<strong>' + escapeHtml(title) + '</strong>';
            if (subtitle) {
                html += '<span>' + escapeHtml(subtitle) + '</span>';
            }
            html += '</span>';
            html += '</button>';
        });

        return '<div class="nbde-image-picker__grid">' + html + '</div>';
    }

    function renderImagePickerModal() {
        var html;
        var currentValue = getScopedValue(state.mediaPicker.scope, state.mediaPicker.path) || '';

        if (!window.icms || !icms.modal || typeof icms.modal.openHtml !== 'function') {
            return false;
        }

        html = '<div class="nbde-image-picker">';
        html += '<div class="nbde-image-picker__head">';
        html += '<div><strong>NordicBlocks media library</strong><span>Выберите изображение для текущего поля или очистите значение.</span></div>';
        html += '<div class="nbde-image-picker__actions"><button class="nbde-mini-button nbde-picker-button" type="button" data-media-picker-action="upload">Загрузить PNG/SVG</button><button class="nbde-mini-button nbde-picker-button nbde-picker-button--ghost" type="button" data-media-picker-action="clear-current">Очистить поле</button></div>';
        html += '</div>';
        if (currentValue) {
            html += '<div class="nbde-image-picker__current">Текущее значение: <span>' + escapeHtml(currentValue) + '</span></div>';
        }
        html += buildImagePickerCards();
        html += '</div>';

        icms.modal.openHtml(html, 'Выбрать изображение');
        return true;
    }

    async function openImagePicker(scope, path) {
        var response;
        var payload;

        state.mediaPicker.kind = 'image';
        state.mediaPicker.scope = scope || '';
        state.mediaPicker.path = path || '';
        state.mediaPicker.error = '';

        if (!renderImagePickerModal()) {
            return false;
        }

        if (state.mediaPicker.items.length || state.mediaPicker.loading) {
            return true;
        }

        state.mediaPicker.loading = true;
        renderImagePickerModal();

        try {
            response = await fetch('/nordicblocks/media_list', {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload && payload.error ? payload.error : 'media_list_failed');
            }

            state.mediaPicker.items = Array.isArray(payload.files) ? payload.files : [];
            state.mediaPicker.error = '';
        } catch (error) {
            state.mediaPicker.items = [];
            state.mediaPicker.error = 'Не удалось загрузить медиабиблиотеку.';
        } finally {
            state.mediaPicker.loading = false;
            renderImagePickerModal();
        }

        return true;
    }

    function triggerImageUpload() {
        var uploadUrl = String(getPath(state, 'editor.mediaUploadUrl', '') || '');
        var input;

        if (!uploadUrl) {
            state.mediaPicker.error = 'Upload endpoint не настроен.';
            renderImagePickerModal();
            return false;
        }

        input = document.createElement('input');
        input.type = 'file';
        input.accept = '.png,.jpg,.jpeg,.gif,.webp,.svg,image/png,image/jpeg,image/gif,image/webp,image/svg+xml';
        input.style.display = 'none';
        input.addEventListener('change', function () {
            var file = input.files && input.files[0] ? input.files[0] : null;

            if (!file) {
                input.remove();
                return;
            }

            uploadImageFromPicker(file).finally(function () {
                input.remove();
            });
        }, { once: true });

        document.body.appendChild(input);
        input.click();
        return true;
    }

    async function uploadImageFromPicker(file) {
        var uploadUrl = String(getPath(state, 'editor.mediaUploadUrl', '') || '');
        var formData = new FormData();
        var response;
        var payload;
        var uploadedUrl;

        if (!uploadUrl) {
            state.mediaPicker.error = 'Upload endpoint не настроен.';
            renderImagePickerModal();
            return false;
        }

        state.mediaPicker.error = '';
        state.mediaPicker.loading = true;
        renderImagePickerModal();
        formData.append('file', file);

        try {
            response = await fetch(uploadUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload && payload.error ? payload.error : 'upload_failed');
            }

            uploadedUrl = getPath(payload, 'media.original', '') || payload.url || '';
            if (!uploadedUrl) {
                throw new Error('upload_failed');
            }

            state.mediaPicker.items = [];
            if (applyScopedValue(state.mediaPicker.scope, state.mediaPicker.path, uploadedUrl)) {
                refreshAfterScopedMutation(state.mediaPicker.scope, state.mediaPicker.path);
            }
            closeSystemModal();
            return true;
        } catch (error) {
            state.mediaPicker.loading = false;
            state.mediaPicker.error = 'Не удалось загрузить файл. Поддерживаются PNG, JPG, WEBP, GIF и SVG.';
            renderImagePickerModal();
            return false;
        }
    }

    function openIconPicker(scope, path) {
        var iconPickerUrl = String(getPath(state, 'editor.iconPickerUrl', '') || '');

        if (!iconPickerUrl) {
            return false;
        }

        if (!window.icms || !icms.modal || typeof icms.modal.openAjax !== 'function') {
            window.open(iconPickerUrl, '_blank');
            return true;
        }

        icms.modal.openAjax(iconPickerUrl, {}, function () {
            Array.prototype.forEach.call(document.querySelectorAll('.icon-select'), function (iconNode) {
                iconNode.addEventListener('click', function (event) {
                    var nextValue;

                    event.preventDefault();
                    nextValue = iconNode.getAttribute('data-name') || '';

                    if (applyScopedValue(scope, path, nextValue)) {
                        refreshAfterScopedMutation(scope, path);
                    }

                    closeSystemModal();
                    return false;
                }, { once: true });
            });
        }, 'Выбрать иконку');

        return true;
    }

    function selectImageFromPicker(index) {
        var item = Array.isArray(state.mediaPicker.items) ? state.mediaPicker.items[index] : null;
        var media = item && item.media ? item.media : null;
        var value = media && (media.original || media.display)
            ? (media.original || media.display)
            : (item && (item.preview_url || item.preview_fallback_url) ? (item.preview_url || item.preview_fallback_url) : '');
        var element = getSelectedElement();
        var branch = element ? currentEditableBranch(element) : null;
        var altValue = media && media.alt ? media.alt : (item && (item.alt || item.title) ? (item.alt || item.title) : '');

        if (!value || !applyScopedValue(state.mediaPicker.scope, state.mediaPicker.path, value)) {
            return false;
        }

        if (state.mediaPicker.scope === 'element-props' && state.mediaPicker.path === 'src' && element && branch && !String(branch.props.alt || '').trim() && altValue) {
            applyScopedValue('element-props', 'alt', altValue);
        }

        refreshAfterScopedMutation(state.mediaPicker.scope, state.mediaPicker.path);
        closeSystemModal();
        return true;
    }

    function applyScopedInput(input) {
        return applyScopedValue(input.dataset.scope, input.dataset.path, coerceValue(input));
    }

    function syncColorControlUi(input) {
        var swatch;
        var preview;
        var textInput;
        var clearButton;
        var isEmpty = !String(input.value || '').trim();

        if (!input || String(input.type || '').toLowerCase() !== 'color') {
            return;
        }

        swatch = input.closest('.nbde-color-swatch');
        preview = swatch ? swatch.querySelector('span') : null;
        textInput = input.closest('.nbde-color-control') ? input.closest('.nbde-color-control').querySelector('input[type="text"]') : null;
        clearButton = input.closest('.nbde-color-control') ? input.closest('.nbde-color-control').querySelector('[data-color-clear]') : null;

        if (preview) {
            preview.style.background = input.value || '';
        }

        if (swatch) {
            swatch.classList.toggle('is-empty', isEmpty);
        }

        if (clearButton) {
            clearButton.classList.toggle('is-empty', isEmpty);
        }

        if (textInput) {
            textInput.value = input.value || '';
        }
    }

    function applyScopedColorPreview(input) {
        if (!input || String(input.type || '').toLowerCase() !== 'color') {
            return false;
        }

        if (!applyScopedInput(input)) {
            return false;
        }

        syncColorControlUi(input);
        markDirty();
        renderCanvas();
        return true;
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

    function getInsertionParentId() {
        if (state.uiState.rootInsertionMode) {
            return '';
        }

        var selected = getSelectedElement();

        if (!selected || getSelectionIds().length !== 1) {
            return '';
        }

        if (selected.type === 'container' || selected.type === 'group') {
            return String(selected.id || '');
        }

        return String(selected.parentId || '');
    }

    function getInsertionOffset(parentId) {
        return getElements().filter(function (element) {
            return String(element.parentId || '') === String(parentId || '');
        }).length * 18;
    }

    function getInsertionZIndex(parentId, breakpoint) {
        var maxZIndex = 0;

        getElements().forEach(function (element) {
            if (String(element.parentId || '') !== String(parentId || '')) {
                return;
            }

            maxZIndex = Math.max(maxZIndex, Number(resolveBranch(element, breakpoint).box.zIndex || 0));
        });

        return maxZIndex + 1;
    }

    function addElement(type) {
        type = normalizeElementType(type);
        var elements = getElements();
        var base = defaultBranch(type);
        var split = splitProps(type, base.props || {});
        var parentId = getInsertionParentId();
        var offset = getInsertionOffset(parentId);
        var element = {
            id: nextElementId(type),
            type: type,
            name: getTypeLabel(type),
            role: '',
            parentId: parentId,
            hidden: false,
            locked: false,
            constraints: { horizontal: 'left', vertical: 'top' },
            transform: {
                x: Number(base.box.x || 0),
                y: Number(base.box.y || 0),
                width: Math.max(1, Number(base.box.w || 1)),
                height: Math.max(1, Number(base.box.h || 1)),
                rotation: Number(base.props.rotate || 0)
            },
            style: split.style,
            content: split.content,
            sharedPropKeys: {}
        };

        ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
            var stageBranch = getPath(state.documentState.contract, 'layout.stage.' + breakpoint, currentStageConfig());
            var stageMetrics = buildStageMetrics(stageBranch, breakpoint);
            var hostSize = getLocalHostSize(element, breakpoint);
            var minX = Number(hostSize.minX || 0);
            var minY = Number(hostSize.minY || 0);
            var maxX = Math.max(minX, Number(hostSize.width || 1) - Math.max(1, Number(base.box.w || 1)));
            var maxY = Math.max(minY, Number(hostSize.height || 1) - Math.max(1, Number(base.box.h || 1)));
            var nextX = parentId ? clamp(offset, minX, maxX) : Number(stageMetrics.initialInsertX || 0) + offset;
            var nextY = parentId ? clamp(offset, minY, maxY) : Number(stageMetrics.initialInsertY || 0) + offset;

            state.scene.layout[breakpoint][element.id] = {
                x: nextX,
                y: nextY,
                width: Math.max(1, Number(base.box.w || 1)),
                height: Math.max(1, Number(base.box.h || 1)),
                rotation: Number(base.props.rotate || 0),
                zIndex: getInsertionZIndex(parentId, breakpoint),
                visible: true
            };
            state.scene.props[breakpoint][element.id] = clone(base.props || {});
        });

        elements.push(element);
        setSelection([element.id], element.id);
        requestInlineFocus(isEditableType(type) ? element.id : null);
        markDirty();
        renderAll();
    }

    function duplicateSelection() {
        var roots = getRootSelectionIds(getSelectionIds());
        var originalElements = getElements();
        var selectedIds = [];
        var originalsById = {};
        var idMap = {};
        var copies = [];

        roots.forEach(function (id) {
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
            if (selectedIds.indexOf(String(element.id)) === -1) {
                return;
            }

            originalsById[String(element.id)] = element;

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
            delete copy.__originId;

            ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
                var originalLayout = clone(state.scene.layout[breakpoint][original.id] || createLayoutEntry(copy, {}, {}));
                var originalProps = clone(state.scene.props[breakpoint][original.id] || {});

                if (roots.indexOf(String(original.id)) >= 0) {
                    originalLayout.x = Number(originalLayout.x || 0) + 24;
                    originalLayout.y = Number(originalLayout.y || 0) + 24;
                }

                state.scene.layout[breakpoint][copy.id] = originalLayout;
                state.scene.props[breakpoint][copy.id] = originalProps;
            });
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

    function removeNodeStores(ids) {
        ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
            ids.forEach(function (id) {
                delete state.scene.layout[breakpoint][id];
                delete state.scene.props[breakpoint][id];
            });
        });
    }

    function deleteSelection() {
        var ids = [];

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

        state.scene.nodes = getElements().filter(function (element) {
            return ids.indexOf(String(element.id)) === -1;
        });
        removeNodeStores(ids);
        clearSelection();
        markDirty();
        renderAll();
    }

    function normalizeZIndices(breakpoint) {
        var ordered = getElements().slice();

        ordered.sort(function (left, right) {
            return Number(resolveBranch(left, breakpoint).box.zIndex || 0) - Number(resolveBranch(right, breakpoint).box.zIndex || 0);
        });

        ordered.forEach(function (element, index) {
            ensureLayoutEntry(element, breakpoint).zIndex = index + 1;
        });
    }

    function shiftSelectionZIndex(delta) {
        var selectedIds = getRootSelectionIds(getSelectionIds());
        var breakpoint = currentBreakpoint();

        if (!selectedIds.length) {
            return;
        }

        selectedIds.forEach(function (id) {
            var element = getElementById(id);
            var layout;

            if (!element) {
                return;
            }

            layout = ensureLayoutEntry(element, breakpoint);
            layout.zIndex = Number(layout.zIndex || 1) + delta;
        });

        normalizeZIndices(breakpoint);
        markDirty();
        renderAll();
    }

    function sendSelectionToEdge(edge) {
        var selectedIds = getRootSelectionIds(getSelectionIds());
        var elements = getElements();
        var max = elements.length + 10;
        var breakpoint = currentBreakpoint();

        if (!selectedIds.length) {
            return;
        }

        selectedIds.forEach(function (id, index) {
            var element = getElementById(id);
            var zIndex = edge === 'front' ? max + index : -selectedIds.length + index;

            if (!element) {
                return;
            }

            ensureLayoutEntry(element, breakpoint).zIndex = zIndex;
        });

        normalizeZIndices(breakpoint);
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

        group = {
            id: nextElementId('group'),
            type: 'group',
            name: 'Группа',
            role: '',
            parentId: parentId,
            hidden: false,
            locked: false,
            constraints: { horizontal: 'left', vertical: 'top' },
            transform: { x: 0, y: 0, width: 360, height: 220, rotation: 0 },
            style: splitProps('group', defaultBranch('group').props).style,
            content: splitProps('group', defaultBranch('group').props).content,
            sharedPropKeys: {}
        };

        ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
            var bounds = null;

            selected.forEach(function (element) {
                var box = resolveBranch(element, breakpoint).box;

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
            state.scene.layout[breakpoint][group.id] = {
                x: bounds.x,
                y: bounds.y,
                width: Math.max(1, bounds.right - bounds.x),
                height: Math.max(1, bounds.bottom - bounds.y),
                rotation: 0,
                zIndex: bounds.zIndex,
                visible: true
            };
            state.scene.props[breakpoint][group.id] = clone(defaultBranch('group').props);
        });

        selected.forEach(function (element) {
            ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
                var layout = ensureLayoutEntry(element, breakpoint);
                layout.x = Number(layout.x || 0) - baseBounds[breakpoint].x;
                layout.y = Number(layout.y || 0) - baseBounds[breakpoint].y;
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
        var children;

        if (!group || group.type !== 'group') {
            return;
        }

        children = getElements().filter(function (element) {
            return String(element.parentId || '') === String(group.id);
        });

        children.forEach(function (child) {
            ['desktop', 'tablet', 'mobile'].forEach(function (breakpoint) {
                var childLayout = ensureLayoutEntry(child, breakpoint);
                var groupLayout = ensureLayoutEntry(group, breakpoint);

                childLayout.x = Number(childLayout.x || 0) + Number(groupLayout.x || 0);
                childLayout.y = Number(childLayout.y || 0) + Number(groupLayout.y || 0);
            });
            child.parentId = String(group.parentId || '');
        });

        state.scene.nodes = getElements().filter(function (element) {
            return String(element.id) !== String(group.id);
        });
        removeNodeStores([group.id]);
        setSelection(children.map(function (child) { return child.id; }), children.length ? children[children.length - 1].id : null);
        markDirty();
        renderAll();
    }

    function uniqueSortedNumbers(values) {
        var seen = {};

        return (values || []).filter(function (value) {
            var rounded = roundNumber(value);
            var key = String(rounded);

            if (seen[key]) {
                return false;
            }

            seen[key] = true;
            return true;
        }).sort(function (left, right) {
            return Number(left) - Number(right);
        });
    }

    function resolveSnapCandidate(value, candidates, threshold) {
        var bestValue = null;
        var bestDistance = Number(threshold) + 1;

        candidates.forEach(function (candidate) {
            var distance = Math.abs(Number(candidate) - Number(value));

            if (distance <= threshold && distance < bestDistance) {
                bestValue = Number(candidate);
                bestDistance = distance;
            }
        });

        return {
            matched: bestValue != null,
            value: bestValue == null ? Number(value) : bestValue,
            guide: bestValue,
            distance: bestValue == null ? null : bestDistance
        };
    }

    function buildLinearCandidates(minValue, maxValue, step) {
        var result = [];
        var size = Math.max(1, Number(step || 8));
        var start = Number(minValue || 0);
        var end = Number(maxValue || 0);
        var cursor;
        var swap;

        if (end < start) {
            swap = start;
            start = end;
            end = swap;
        }

        result.push(start);
        result.push(end);

        cursor = Math.ceil(start / size) * size;
        for (; cursor <= end; cursor += size) {
            result.push(cursor);
        }

        return uniqueSortedNumbers(result);
    }

    function buildStageXCandidates(stageMetrics, editorRuntime) {
        var result = buildLinearCandidates(-stageMetrics.bleedLeft, stageMetrics.width + stageMetrics.bleedRight, editorRuntime.gridSize);
        var cursor = 0;
        var index;

        result.push(0);
        result.push(stageMetrics.width);

        for (index = 0; index < stageMetrics.columns; index++) {
            result.push(cursor);
            cursor += stageMetrics.columnWidth;
            result.push(cursor);

            if (index < stageMetrics.columns - 1) {
                result.push(cursor + stageMetrics.gutter);
                cursor += stageMetrics.gutter;
            }
        }

        return uniqueSortedNumbers(result);
    }

    function buildLocalAlignmentNodeSnapshot(breakpoint) {
        return getElements().map(function (candidate) {
            var branch = resolveBranch(candidate, breakpoint);

            return {
                id: candidate.id,
                parentId: candidate.parentId || '',
                hidden: candidate.hidden,
                visible: branch.box.visible !== false,
                box: {
                    x: Number(branch.box.x || 0),
                    y: Number(branch.box.y || 0),
                    w: Math.max(1, Number(branch.box.w || 1)),
                    h: Math.max(1, Number(branch.box.h || 1))
                }
            };
        });
    }

    function buildElementAlignmentCandidates(element, axis, excludedIds) {
        return InteractionCore.buildSiblingAlignmentCandidates(
            buildLocalAlignmentNodeSnapshot(currentBreakpoint()),
            element ? element.id : '',
            axis,
            {
                excludeIds: excludedIds || [],
                includeCenters: true
            }
        );
    }

    function localGuideToWorld(element, axis, localPosition, breakpoint) {
        var parent;
        var parentBox;
        var parentOffset;

        if (localPosition == null) {
            return null;
        }

        parent = element && element.parentId ? getElementById(element.parentId) : null;
        if (!parent) {
            return Number(localPosition);
        }

        parentBox = getAbsoluteWorldBox(parent, breakpoint);
        parentOffset = getParentContentOffset(parent, breakpoint);

        return axis === 'y'
            ? Number(parentBox.y || 0) + Number(parentOffset.y || 0) + Number(localPosition)
            : Number(parentBox.x || 0) + Number(parentOffset.x || 0) + Number(localPosition);
    }

    function buildHorizontalSnapCandidates(element, hostSize, editorRuntime, excludedIds) {
        var result;

        if (element && !element.parentId) {
            result = buildStageXCandidates(currentStageMetrics(), editorRuntime);
        } else {
            result = buildLinearCandidates(Number(hostSize.minX || 0), Number(hostSize.width || 0), editorRuntime.gridSize);
        }

        return uniqueSortedNumbers(result.concat(buildElementAlignmentCandidates(element, 'x', excludedIds)));
    }

    function buildVerticalSnapCandidates(element, hostSize, editorRuntime, excludedIds) {
        return uniqueSortedNumbers(
            buildLinearCandidates(Number(hostSize.minY || 0), Number(hostSize.height || 0), editorRuntime.gridSize)
                .concat(buildElementAlignmentCandidates(element, 'y', excludedIds))
        );
    }

    function getViewportNode() {
        return document.getElementById('nbd-stage-viewport');
    }

    function getViewportRect() {
        var viewportNode = getViewportNode();
        return viewportNode ? viewportNode.getBoundingClientRect() : null;
    }

    function getScreenPointFromEvent(event) {
        var rect = getViewportRect();

        if (!rect) {
            return null;
        }

        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top
        };
    }

    function getWorldPointFromEvent(event) {
        var screenPoint = getScreenPointFromEvent(event);
        return screenPoint ? screenToWorldPoint(screenPoint.x, screenPoint.y) : null;
    }

    function updatePointerTelemetry(event) {
        var screenPoint = getScreenPointFromEvent(event);
        var worldPoint;

        if (!screenPoint) {
            return;
        }

        worldPoint = screenToWorldPoint(screenPoint.x, screenPoint.y);
        state.interactionState.pointerTelemetry = {
            pointerId: event && event.pointerId != null ? String(event.pointerId) : '',
            screenX: screenPoint.x,
            screenY: screenPoint.y,
            worldX: worldPoint.x,
            worldY: worldPoint.y
        };

        if (geometryDebugEnabled) {
            renderStageCard();
        }
    }

    function acquirePointerCapture(event, mode, meta) {
        var capture = InteractionCore.createPointerCaptureSession(event && event.pointerId, mode, meta);

        if (!capture) {
            return null;
        }

        state.interactionState.pointerCapture = capture;

        if (root && root.setPointerCapture) {
            try {
                root.setPointerCapture(event.pointerId);
            } catch (error) {
                // Ignore browsers that reject capture when the pointer is already released.
            }
        }

        return capture;
    }

    function releasePointerCapture(pointerId) {
        var capture = state.interactionState.pointerCapture;

        if (!capture || !InteractionCore.isPointerCaptureMatch(capture, pointerId)) {
            return;
        }

        if (root && root.releasePointerCapture) {
            try {
                root.releasePointerCapture(Number(pointerId));
            } catch (error) {
                // Ignore release failures after DOM re-render.
            }
        }

        state.interactionState.pointerCapture = InteractionCore.releasePointerCaptureSession(capture, pointerId);
    }

    function isCapturedPointerEvent(event, mode) {
        return InteractionCore.isPointerCaptureMatch(state.interactionState.pointerCapture, event && event.pointerId, mode);
    }

    function zoomTo(screenPoint, nextZoom) {
        state.scene.viewport = GeometryCore.zoomViewportAtScreenPoint(state.scene.viewport, screenPoint, nextZoom, {
            minZoom: 0.25,
            maxZoom: 2
        });
        clearGuides();
        renderCanvas();
        renderStageCard();
    }

    function resetViewport() {
        state.scene.viewport.zoom = 1;
        state.scene.viewport.offsetX = 0;
        state.scene.viewport.offsetY = 0;
        clearGuides();
        renderCanvas();
        renderStageCard();
    }

    function beginPan(event) {
        acquirePointerCapture(event, 'pan', null);
        state.interactionState.pan = {
            startClientX: event.clientX,
            startClientY: event.clientY,
            startOffsetX: Number(state.scene.viewport.offsetX || 0),
            startOffsetY: Number(state.scene.viewport.offsetY || 0)
        };
        clearGuides();
        renderCanvas();
    }

    function hitTestWorldPoint(worldPoint, breakpoint) {
        var hitId = InteractionCore.hitTestWorldPoint(getElements().map(function (element) {
            var box = getAbsoluteWorldBox(element, breakpoint);
            var branch = resolveBranch(element, breakpoint);

            return {
                id: element.id,
                parentId: element.parentId || '',
                hidden: element.hidden,
                visible: branch.box.visible !== false,
                zIndex: getAbsoluteZIndex(element, breakpoint),
                box: box
            };
        }), worldPoint);

        return hitId ? getElementById(hitId) : null;
    }

    function beginDrag(elementId, event, worldPoint) {
        var clickedSelected = isSelected(elementId);
        var nodeIds = clickedSelected ? getRootSelectionIds(getSelectionIds()) : [String(elementId)];
        var startBoxes = {};

        if (!worldPoint) {
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
        acquirePointerCapture(event, 'drag', { elementId: String(elementId) });

        state.interactionState.drag = {
            nodeIds: nodeIds,
            primaryId: String(elementId),
            startWorldX: worldPoint.x,
            startWorldY: worldPoint.y,
            startBoxes: startBoxes
        };

        renderLayersCard();
        renderPropertiesCard();
        renderCanvas();
    }

    function beginResize(elementId, handle, event, worldPoint) {
        var element = getElementById(elementId);
        var branchData = element ? currentEditableBranch(element) : null;
        var childBoxes = {};

        if (!element || !branchData || getSelectionIds().length !== 1 || !worldPoint) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        acquirePointerCapture(event, 'resize', { elementId: String(elementId), handle: String(handle || '') });

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
            startWorldX: worldPoint.x,
            startWorldY: worldPoint.y,
            startBox: {
                x: Number(branchData.box.x || 0),
                y: Number(branchData.box.y || 0),
                w: Number(branchData.box.w || 1),
                h: Number(branchData.box.h || 1)
            },
            childBoxes: childBoxes
        };

        renderPropertiesCard();
        renderCanvas();
    }

    function beginRotate(elementId, event, worldPoint) {
        var element = getElementById(elementId);
        var branchData = element ? currentEditableBranch(element) : null;
        var absoluteBox;
        var center;

        if (!element || !branchData || getSelectionIds().length !== 1 || !worldPoint) {
            return;
        }

        absoluteBox = getAbsoluteWorldBox(element, currentBreakpoint());
        center = {
            x: Number(absoluteBox.x || 0) + (Number(absoluteBox.w || 0) / 2),
            y: Number(absoluteBox.y || 0) + (Number(absoluteBox.h || 0) / 2)
        };

        event.preventDefault();
        event.stopPropagation();
        acquirePointerCapture(event, 'rotate', { elementId: String(elementId) });

        state.interactionState.rotate = {
            elementId: String(elementId),
            centerX: center.x,
            centerY: center.y,
            startAngle: Math.atan2(worldPoint.y - center.y, worldPoint.x - center.x) * (180 / Math.PI),
            startRotation: Number(branchData.box.rotation || 0)
        };

        renderPropertiesCard();
        renderCanvas();
    }

    function beginTextIntent(elementId, event, worldPoint) {
        if (!elementId || !worldPoint) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        acquirePointerCapture(event, 'text-intent', { elementId: String(elementId) });

        state.interactionState.textIntent = {
            elementId: String(elementId),
            startClientX: Number(event.clientX || 0),
            startClientY: Number(event.clientY || 0),
            startWorldX: Number(worldPoint.x || 0),
            startWorldY: Number(worldPoint.y || 0),
            moved: false
        };
    }

    function resolveTextIntentMove(event) {
        var intent = state.interactionState.textIntent;
        var worldPoint;
        var distanceX;
        var distanceY;

        if (!intent || !isCapturedPointerEvent(event, 'text-intent')) {
            return false;
        }

        distanceX = Math.abs(Number(event.clientX || 0) - Number(intent.startClientX || 0));
        distanceY = Math.abs(Number(event.clientY || 0) - Number(intent.startClientY || 0));

        if (Math.max(distanceX, distanceY) < 4) {
            return true;
        }

        worldPoint = getWorldPointFromEvent(event);
        intent.moved = true;
        state.interactionState.textIntent = null;
        beginDrag(intent.elementId, event, {
            x: Number(intent.startWorldX || 0),
            y: Number(intent.startWorldY || 0)
        });
        applyDragAtWorldPoint(state.interactionState.drag, worldPoint);
        return true;
    }

    function nudgeSelection(deltaX, deltaY) {
        var selectedIds = getRootSelectionIds(getSelectionIds());
        var breakpoint = currentBreakpoint();
        var moved = false;

        if (!selectedIds.length) {
            return false;
        }

        selectedIds.forEach(function (id) {
            var element = getElementById(id);
            var branchData = element ? currentEditableBranch(element) : null;
            var hostSize;
            var interactionBounds;
            var minSize;
            var minX;
            var minY;
            var maxX;
            var maxY;

            if (!element || !branchData) {
                return;
            }

            hostSize = getLocalHostSize(element, breakpoint);
            interactionBounds = getInteractionBounds(element, breakpoint);
            minSize = getMinBoxSize(element, composeBreakpointProps(element, breakpoint));

            branchData.box.x = Number(branchData.box.x || 0) + Number(deltaX || 0);
            branchData.box.y = Number(branchData.box.y || 0) + Number(deltaY || 0);

            if (interactionBounds) {
                minX = Number(hostSize.minX || 0);
                minY = Number(hostSize.minY || 0);
                maxX = Number(hostSize.width || 1) - Number(minSize.w || 1);
                maxY = Number(hostSize.height || 1) - Number(minSize.h || 1);
                branchData.box.x = clamp(branchData.box.x, minX, maxX);
                branchData.box.y = clamp(branchData.box.y, minY, maxY);
            }

            moved = true;
        });

        if (!moved) {
            return false;
        }

        markDirty();
        renderPropertiesCard();
        renderCanvas();
        return true;
    }

    function applyDragAtWorldPoint(drag, worldPoint) {
        var editorRuntime = currentEditorRuntime();
        var primaryStart;
        var primaryElement;
        var hostSize;
        var interactionBounds;
        var nextPrimaryBox;
        var nextX;
        var nextY;
        var deltaX;
        var deltaY;
        var xCandidates;
        var yCandidates;
        var xSnap;
        var ySnap;

        if (!drag || !worldPoint) {
            return false;
        }

        primaryStart = drag.startBoxes[drag.primaryId];
        primaryElement = getElementById(drag.primaryId);

        if (!primaryStart || !primaryElement) {
            return false;
        }

        hostSize = getLocalHostSize(primaryElement, currentBreakpoint());
        interactionBounds = getInteractionBounds(primaryElement, currentBreakpoint());
        nextPrimaryBox = GeometryCore.applyDragSession({
            startBox: primaryStart,
            startPointerWorld: {
                x: drag.startWorldX,
                y: drag.startWorldY
            }
        }, worldPoint, {
            bounds: interactionBounds
        });
        nextX = nextPrimaryBox.x;
        nextY = nextPrimaryBox.y;

        if (editorRuntime.snapToGrid) {
            xCandidates = buildHorizontalSnapCandidates(primaryElement, hostSize, editorRuntime, drag.nodeIds);
            yCandidates = buildVerticalSnapCandidates(primaryElement, hostSize, editorRuntime, drag.nodeIds);
            xSnap = InteractionCore.resolveAxisAlignment(nextX, nextPrimaryBox.w, xCandidates, Number(editorRuntime.snapThreshold || 6), { includeCenter: true });
            ySnap = InteractionCore.resolveAxisAlignment(nextY, nextPrimaryBox.h, yCandidates, Number(editorRuntime.snapThreshold || 6), { includeCenter: true });
            nextX = xSnap.value;
            nextY = ySnap.value;
            state.interactionState.guideX = localGuideToWorld(primaryElement, 'x', xSnap.guide, currentBreakpoint());
            state.interactionState.guideY = localGuideToWorld(primaryElement, 'y', ySnap.guide, currentBreakpoint());
        } else {
            state.interactionState.guideX = null;
            state.interactionState.guideY = null;
        }

        deltaX = nextX - primaryStart.x;
        deltaY = nextY - primaryStart.y;

        drag.nodeIds.forEach(function (id) {
            var element = getElementById(id);
            var branchData = element ? currentEditableBranch(element) : null;
            var startBox = drag.startBoxes[id];
            var localHostSize;
            var localInteractionBounds;
            var nextBox;

            if (!branchData || !startBox || !element) {
                return;
            }

            localHostSize = getLocalHostSize(element, currentBreakpoint());
            localInteractionBounds = getInteractionBounds(element, currentBreakpoint());
            nextBox = GeometryCore.applyDragSession({
                startBox: startBox,
                startPointerWorld: {
                    x: 0,
                    y: 0
                }
            }, {
                x: deltaX,
                y: deltaY
            }, {
                bounds: localInteractionBounds
            });
            branchData.box.x = nextBox.x;
            branchData.box.y = nextBox.y;
        });
        markDirty();
        renderPropertiesCard();
        renderCanvas();
        return true;
    }

    function handleDragMove(event) {
        var drag = state.interactionState.drag;
        var worldPoint;

        if (!drag || !isCapturedPointerEvent(event, 'drag')) {
            return;
        }

        worldPoint = getWorldPointFromEvent(event);
        applyDragAtWorldPoint(drag, worldPoint);
    }

    function handleTextIntentMove(event) {
        resolveTextIntentMove(event);
    }

    function applyResizeAtWorldPoint(resize, worldPoint) {
        var editorRuntime = currentEditorRuntime();
        var element;
        var branchData;
        var props;
        var minSize;
        var handle;
        var hostSize;
        var xCandidates;
        var yCandidates;
        var nextBox;
        var nextRight;
        var nextBottom;
        var scaleX;
        var scaleY;
        var threshold;
        var xSnap;
        var ySnap;
        var minX;
        var minY;
        var interactionBounds;

        if (!resize || !worldPoint) {
            return false;
        }

        element = getElementById(resize.elementId);
        branchData = element ? currentEditableBranch(element) : null;

        if (!element || !branchData) {
            return false;
        }

        props = composeBreakpointProps(element, currentBreakpoint());
        minSize = getMinBoxSize(element, props);
        handle = resize.handle;
        hostSize = getLocalHostSize(element, currentBreakpoint());
        interactionBounds = getInteractionBounds(element, currentBreakpoint());
        threshold = Number(editorRuntime.snapThreshold || 6);

        nextBox = GeometryCore.applyResizeSession({
            startBox: resize.startBox,
            handle: handle,
            startPointerWorld: {
                x: resize.startWorldX,
                y: resize.startWorldY
            }
        }, worldPoint, {
            bounds: interactionBounds,
            minWidth: minSize.w,
            minHeight: minSize.h,
            keepAspectRatio: (element.type === 'svg' || element.type === 'video') && handle.length === 2
        });

        if (editorRuntime.snapToGrid) {
            xCandidates = buildHorizontalSnapCandidates(element, hostSize, editorRuntime, [element.id]);
            yCandidates = buildVerticalSnapCandidates(element, hostSize, editorRuntime, [element.id]);
            nextRight = nextBox.x + nextBox.w;
            nextBottom = nextBox.y + nextBox.h;

            if (handle.indexOf('w') >= 0) {
                xSnap = resolveSnapCandidate(nextBox.x, xCandidates, threshold);
                nextBox.x = xSnap.value;
            }
            if (handle.indexOf('e') >= 0) {
                xSnap = resolveSnapCandidate(nextRight, xCandidates, threshold);
                nextRight = xSnap.value;
            }
            if (handle.indexOf('n') >= 0) {
                ySnap = resolveSnapCandidate(nextBox.y, yCandidates, threshold);
                nextBox.y = ySnap.value;
            }
            if (handle.indexOf('s') >= 0) {
                ySnap = resolveSnapCandidate(nextBottom, yCandidates, threshold);
                nextBottom = ySnap.value;
            }

            nextBox.w = Math.max(minSize.w, nextRight - nextBox.x);
            nextBox.h = Math.max(minSize.h, nextBottom - nextBox.y);
            state.interactionState.guideX = xSnap && xSnap.guide != null
                ? localGuideToWorld(element, 'x', xSnap.guide, currentBreakpoint())
                : null;
            state.interactionState.guideY = ySnap && ySnap.guide != null
                ? localGuideToWorld(element, 'y', ySnap.guide, currentBreakpoint())
                : null;
        } else {
            state.interactionState.guideX = null;
            state.interactionState.guideY = null;
        }

        if (interactionBounds) {
            minX = Number(hostSize.minX || 0);
            minY = Number(hostSize.minY || 0);
            nextBox.x = clamp(nextBox.x, minX, hostSize.width - minSize.w);
            nextBox.y = clamp(nextBox.y, minY, hostSize.height - minSize.h);
            nextRight = clamp(nextBox.x + nextBox.w, nextBox.x + minSize.w, hostSize.width);
            nextBottom = clamp(nextBox.y + nextBox.h, nextBox.y + minSize.h, hostSize.height);
            nextBox.w = Math.max(minSize.w, Math.round(nextRight - nextBox.x));
            nextBox.h = Math.max(minSize.h, Math.round(nextBottom - nextBox.y));
        }

        branchData.box.x = nextBox.x;
        branchData.box.y = nextBox.y;
        branchData.box.w = nextBox.w;
        branchData.box.h = nextBox.h;

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

        state.interactionState.guideX = state.interactionState.guideX != null
            ? state.interactionState.guideX
            : (handle.indexOf('w') >= 0 ? getAbsoluteWorldBox(element, currentBreakpoint()).x : (handle.indexOf('e') >= 0 ? (getAbsoluteWorldBox(element, currentBreakpoint()).x + branchData.box.w) : null));
        state.interactionState.guideY = state.interactionState.guideY != null
            ? state.interactionState.guideY
            : (handle.indexOf('n') >= 0 ? getAbsoluteWorldBox(element, currentBreakpoint()).y : (handle.indexOf('s') >= 0 ? (getAbsoluteWorldBox(element, currentBreakpoint()).y + branchData.box.h) : null));
        markDirty();
        renderPropertiesCard();
        renderCanvas();

        if (element.type === 'text' && nodes.canvasStage) {
            syncInlineTextHeight(element.id, nodes.canvasStage.querySelector('.nbde-el[data-element-id="' + selectorEscape(element.id) + '"] .nbde-el__body--text'), true);
            renderPropertiesCard();
        }

        return true;
    }

    function handleResizeMove(event) {
        var resize = state.interactionState.resize;
        var worldPoint;

        if (!resize || !isCapturedPointerEvent(event, 'resize')) {
            return;
        }

        worldPoint = getWorldPointFromEvent(event);
        applyResizeAtWorldPoint(resize, worldPoint);
    }

    function handleRotateMove(event) {
        var rotate = state.interactionState.rotate;
        var element;
        var branchData;
        var worldPoint;
        var angle;
        var nextRotation;

        if (!rotate || !isCapturedPointerEvent(event, 'rotate')) {
            return;
        }

        worldPoint = getWorldPointFromEvent(event);
        element = getElementById(rotate.elementId);
        branchData = element ? currentEditableBranch(element) : null;

        if (!worldPoint || !element || !branchData) {
            return;
        }

        angle = Math.atan2(worldPoint.y - rotate.centerY, worldPoint.x - rotate.centerX) * (180 / Math.PI);
        nextRotation = Number(rotate.startRotation || 0) + (angle - Number(rotate.startAngle || 0));

        if (!event.shiftKey) {
            nextRotation = Math.round(nextRotation / 5) * 5;
        }

        branchData.box.rotation = nextRotation;
        markDirty();
        renderPropertiesCard();
        renderCanvas();
    }

    function handleStageResizeMove(event) {
        var stageResize = state.interactionState.stageResize;

        if (!stageResize) {
            return;
        }

        applyStageResizeAtClientY(stageResize, event.clientY);
    }

    function handlePanMove(event) {
        var pan = state.interactionState.pan;
        var zoom;

        if (!pan || !isCapturedPointerEvent(event, 'pan')) {
            return;
        }

        zoom = Math.max(0.01, Number(state.scene.viewport.zoom || 1));
        state.scene.viewport.offsetX = Number(pan.startOffsetX || 0) - ((event.clientX - pan.startClientX) / zoom);
        state.scene.viewport.offsetY = Number(pan.startOffsetY || 0) - ((event.clientY - pan.startClientY) / zoom);
        renderCanvas();
        renderStageCard();
    }

    function finishInteraction(event) {
        var pointerId = event && event.pointerId != null
            ? String(event.pointerId)
            : (state.interactionState.pointerCapture ? String(state.interactionState.pointerCapture.pointerId) : '');

        if (event && state.interactionState.pointerCapture && !InteractionCore.isPointerCaptureMatch(state.interactionState.pointerCapture, pointerId)) {
            return;
        }

        if (!state.interactionState.drag && !state.interactionState.resize && !state.interactionState.rotate && !state.interactionState.textIntent && !state.interactionState.pan && !state.interactionState.stageResize && !state.interactionState.numberScrub) {
            return;
        }

        if (state.interactionState.textIntent && (!event || !event.type || event.type === 'pointerup' || event.type === 'mouseup')) {
            requestInlineFocus(state.interactionState.textIntent.elementId);
            state.interactionState.textIntent = null;
            releasePointerCapture(pointerId);
            renderPropertiesCard();
            renderCanvas();
            return;
        }

        releasePointerCapture(pointerId);
        state.interactionState.drag = null;
        state.interactionState.resize = null;
        state.interactionState.rotate = null;
        state.interactionState.textIntent = null;
        state.interactionState.pan = null;
        state.interactionState.numberScrub = null;
        state.interactionState.stageResize = null;
        clearGuides();
        renderPropertiesCard();
        renderCanvas();
        renderStageCard();
    }

    function applyToolbarAction(action) {
        var element = getSelectedElement();

        if (!element) {
            return false;
        }

        if (action === 'toolbar-text-increase') {
            writeNodeSharedProp(element, 'fontSize', Number(composeBreakpointProps(element, currentBreakpoint()).fontSize || (element.type === 'button' ? 16 : 36)) + 2);
            return true;
        }
        if (action === 'toolbar-text-decrease') {
            writeNodeSharedProp(element, 'fontSize', Math.max(10, Number(composeBreakpointProps(element, currentBreakpoint()).fontSize || (element.type === 'button' ? 16 : 36)) - 2));
            return true;
        }
        if (action === 'toolbar-radius-increase') {
            writeNodeSharedProp(element, 'borderRadius', Number(composeBreakpointProps(element, currentBreakpoint()).borderRadius || 0) + 4);
            return true;
        }
        if (action === 'toolbar-radius-decrease') {
            writeNodeSharedProp(element, 'borderRadius', Math.max(0, Number(composeBreakpointProps(element, currentBreakpoint()).borderRadius || 0) - 4));
            return true;
        }

        return false;
    }

    function applyToolbarColor(target) {
        var element = getSelectedElement();
        var role = target.dataset.toolbarColor;

        if (!element || !role) {
            return false;
        }

        if (role === 'text') {
            writeNodeSharedProp(element, 'color', target.value);
            return true;
        }

        if (role === 'fill') {
            writeNodeSharedProp(element, 'backgroundColor', target.value);
            writeNodeSharedProp(element, 'fill', target.value);
            return true;
        }

        return false;
    }

    function buildDebugSnapshot() {
        return {
            breakpoint: currentBreakpoint(),
            viewport: clone(state.scene.viewport),
            selectionIds: getSelectionIds().slice(),
            selectedElementId: state.uiState.selectedElementId,
            pointerCapture: clone(state.interactionState.pointerCapture),
            pointerTelemetry: clone(state.interactionState.pointerTelemetry),
            nodes: getElements().map(function (element) {
                return {
                    id: element.id,
                    type: element.type,
                    box: resolveBranch(element, currentBreakpoint()).box,
                    absoluteBox: getAbsoluteWorldBox(element, currentBreakpoint())
                };
            })
        };
    }

    function createSyntheticEvent() {
        return {
            preventDefault: function () {},
            stopPropagation: function () {}
        };
    }

    function attachDebugApi() {
        if (!geometryDebugEnabled) {
            try {
                delete window.NordicblocksDesignBlockDebug;
            } catch (error) {
                window.NordicblocksDesignBlockDebug = undefined;
            }
            return;
        }

        window.NordicblocksDesignBlockDebug = {
            snapshot: function () {
                return buildDebugSnapshot();
            },
            projectWorldPoint: function (worldX, worldY) {
                return projectWorldPoint(state.scene.viewport, worldX, worldY);
            },
            screenToWorldPoint: function (screenX, screenY) {
                return screenToWorldPoint(screenX, screenY);
            },
            zoomToValue: function (value) {
                zoomTo(null, Number(value || 1));
                return buildDebugSnapshot();
            },
            dragSelectionByScreen: function (screenDx, screenDy) {
                var element = getSelectedElement();
                var absoluteBox;
                var startScreen;
                var startWorld;

                if (!element) {
                    return null;
                }

                absoluteBox = getAbsoluteWorldBox(element, currentBreakpoint());
                startScreen = projectWorldPoint(state.scene.viewport, absoluteBox.x + (absoluteBox.w / 2), absoluteBox.y + (absoluteBox.h / 2));
                startWorld = screenToWorldPoint(startScreen.x, startScreen.y);
                beginDrag(element.id, createSyntheticEvent(), startWorld);
                applyDragAtWorldPoint(state.interactionState.drag, screenToWorldPoint(startScreen.x + Number(screenDx || 0), startScreen.y + Number(screenDy || 0)));
                finishInteraction();
                return buildDebugSnapshot();
            },
            resizeSelectionEastByScreen: function (screenDx) {
                var element = getSelectedElement();
                var absoluteBox;
                var startScreen;
                var startWorld;

                if (!element) {
                    return null;
                }

                absoluteBox = getAbsoluteWorldBox(element, currentBreakpoint());
                startScreen = projectWorldPoint(state.scene.viewport, absoluteBox.x + absoluteBox.w, absoluteBox.y + (absoluteBox.h / 2));
                startWorld = screenToWorldPoint(startScreen.x, startScreen.y);
                beginResize(element.id, 'e', createSyntheticEvent(), startWorld);
                applyResizeAtWorldPoint(state.interactionState.resize, screenToWorldPoint(startScreen.x + Number(screenDx || 0), startScreen.y));
                finishInteraction();
                return buildDebugSnapshot();
            },
            hitTestScreen: function (screenX, screenY) {
                var element = hitTestWorldPoint(screenToWorldPoint(screenX, screenY), currentBreakpoint());
                return element ? element.id : null;
            }
        };
    }

    async function saveContract(options) {
        options = options || {};

        var response;
        var payload;
        var serializedContract;
        var saveRevision;
        var normalizedContract;
        var requestedMode = options.mode === 'autosave' ? 'autosave' : 'manual';

        if (!state.editor.saveUrl || !state.documentState.contract || !state.documentState.block) {
            return;
        }

        if (state.uiState.isSaving) {
            state.uiState.pendingSaveMode = requestedMode === 'manual' ? 'manual' : (state.uiState.pendingSaveMode || 'autosave');
            return;
        }

        clearAutosaveTimer();
        state.uiState.isAutosaveScheduled = false;

        state.uiState.isSaving = true;
        state.uiState.saveMode = requestedMode;
        state.uiState.lastError = '';
        renderStatus();

        serializedContract = serializeSceneIntoContract(state.documentState.contract);
        saveRevision = state.uiState.changeRevision;

        try {
            response = await fetch(state.editor.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: buildSaveRequestBody(serializedContract)
            });
            payload = await response.json();

            if (!response.ok || !payload.ok) {
                throw new Error(payload.error || 'save_failed');
            }

            normalizedContract = normalizeContract(payload.contract || serializedContract);
            state.documentState.lastSavedAt = new Date().toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            state.uiState.lastError = '';

            if (state.uiState.changeRevision === saveRevision) {
                state.documentState.contract = normalizedContract;
                state.scene = buildSceneFromContract(state.documentState.contract);
                state.uiState.isDirty = false;
                renderAll();
            } else {
                state.uiState.isDirty = true;
                renderStatus();
            }
        } catch (error) {
            state.uiState.lastError = 'Ошибка сохранения: ' + (error && error.message ? error.message : 'неизвестная ошибка');
            renderStatus();
        } finally {
            state.uiState.isSaving = false;
            state.uiState.saveMode = 'manual';
            renderStatus();

            if (state.uiState.pendingSaveMode) {
                requestedMode = state.uiState.pendingSaveMode;
                state.uiState.pendingSaveMode = '';
                window.setTimeout(function () {
                    saveContract({ mode: requestedMode });
                }, 0);
            } else if (state.uiState.isDirty) {
                scheduleAutosave();
                renderStatus();
            }
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
            state.scene = buildSceneFromContract(state.documentState.contract);
            state.palette = getPath(payload, 'palette.items', []).filter(function (item) {
                return item && item.type !== 'group' && isPaletteTypeEnabled(item.type);
            });
            state.pickers = payload.pickers || {};
            state.typography.fontFamilies = normalizeTypographyFamilies(getPath(payload, 'typography.fontFamilies', state.typography.fontFamilies));
            state.typography.fontFaceCss = String(getPath(payload, 'typography.fontFaceCss', state.typography.fontFaceCss) || '');
            state.editor.saveUrl = getPath(payload, 'editor.saveUrl', state.editor.saveUrl);
            state.editor.placeUrl = getPath(payload, 'editor.placeUrl', state.editor.placeUrl);
            state.editor.backUrl = getPath(payload, 'editor.backUrl', state.editor.backUrl);
            state.editor.csrfToken = getPath(payload, 'editor.csrfToken', state.editor.csrfToken);
            ensureTypographyStyles();
            state.uiState.activeBreakpoint = getPath(payload, 'ui.activeBreakpoint', state.uiState.activeBreakpoint) || 'desktop';
            clearAutosaveTimer();
            state.uiState.isDirty = false;
            state.uiState.isAutosaveScheduled = false;
            state.uiState.pendingSaveMode = '';
            state.uiState.saveMode = 'manual';
            state.uiState.changeRevision = 0;
            state.uiState.lastError = '';
            state.uiState.editingTextId = null;
            state.uiState.pendingFocusTextId = null;
            clearGuides();
            if (getPath(payload, 'ui.selectionIds', []).length) {
                setSelection(getPath(payload, 'ui.selectionIds', []), getPath(payload, 'ui.selectedElementId', null));
            } else {
                state.uiState.selectionIds = [];
                state.uiState.selectedElementId = null;
                state.uiState.rootInsertionMode = false;
            }
            renderAll();
        } catch (error) {
            state.uiState.lastError = 'Ошибка загрузки редактора: ' + (error && error.message ? error.message : 'неизвестная ошибка');
            renderStatus();
        }
    }

    root.addEventListener('click', function (event) {
        var actionNode = event.target.closest('[data-action]');
        var pickerNode = event.target.closest('[data-picker-action]');
        var action;

        if (pickerNode) {
            event.preventDefault();
            if (pickerNode.dataset.pickerAction === 'clear') {
                if (applyScopedValue(pickerNode.dataset.scope || '', pickerNode.dataset.path || '', '')) {
                    refreshAfterScopedMutation(pickerNode.dataset.scope || '', pickerNode.dataset.path || '');
                }
                return;
            }
            if (pickerNode.dataset.pickerAction === 'open' && pickerNode.dataset.pickerKind === 'image') {
                openImagePicker(pickerNode.dataset.scope || '', pickerNode.dataset.path || '');
                return;
            }
            if (pickerNode.dataset.pickerAction === 'open' && pickerNode.dataset.pickerKind === 'icon') {
                openIconPicker(pickerNode.dataset.scope || '', pickerNode.dataset.path || '');
                return;
            }
        }

        if (!actionNode) {
            return;
        }

        action = actionNode.dataset.action;

        if (action === 'toggle-add-menu') {
            state.uiState.addMenuOpen = !state.uiState.addMenuOpen;
            renderBlockCard();
            return;
        }

        if (action === 'toggle-stage-card') {
            toggleStageCard();
            return;
        }

        if (action === 'toggle-section-card') {
            toggleSectionCard();
            return;
        }

        if (action === 'toggle-layers-card') {
            toggleLayersCard();
            return;
        }

        if (action === 'add-element') {
            state.uiState.addMenuOpen = false;
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
            revealSelectionInViewport(false);
            renderAll();
            return;
        }
        if (action === 'select-parent') {
            focusParentSelection();
            revealSelectionInViewport(false);
            renderAll();
            return;
        }
        if (action === 'select-root-context') {
            clearSelection();
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
        if (action === 'zoom-in') {
            zoomTo(null, Number(state.scene.viewport.zoom || 1) * 1.15);
            return;
        }
        if (action === 'zoom-out') {
            zoomTo(null, Number(state.scene.viewport.zoom || 1) / 1.15);
            return;
        }
        if (action === 'focus-artboard') {
            focusArtboard();
            return;
        }
        if (action === 'zoom-reset' || action === 'camera-reset') {
            resetViewport();
            return;
        }
        if (action === 'apply-stage-height-all') {
            applyStageHeightToAllBreakpoints();
            return;
        }
        if (action === 'focus-stage-zone') {
            focusStageZone(actionNode.dataset.zone || 'content');
            return;
        }
        if (action === 'reveal-selection') {
            if (revealSelectionInViewport(true)) {
                clearGuides();
                renderCanvas();
                renderStageCard();
            }
            return;
        }
        if (action === 'toggle-element-visibility') {
            toggleElementVisibility(actionNode.dataset.elementId || '');
            return;
        }
        if (action === 'toggle-element-lock') {
            toggleElementLock(actionNode.dataset.elementId || '');
            return;
        }
        if (applyToolbarAction(action)) {
            markDirty();
            renderAll();
        }
    });

    root.addEventListener('dblclick', function (event) {
        var stageViewport = event.target.closest('#nbd-stage-viewport');
        var worldPoint;
        var element;

        if (!stageViewport || event.target.closest('[data-inline-edit="text"]')) {
            return;
        }

        worldPoint = getWorldPointFromEvent(event);
        element = worldPoint ? hitTestWorldPoint(worldPoint, currentBreakpoint()) : null;

        if (!element || element.locked || !isEditableType(element.type)) {
            return;
        }

        setSelection([element.id], element.id);
        revealSelectionInViewport(false);
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

            writeNodeSharedProp(inlineElement, 'text', normalizeInlineText(target));
            syncInlineTextHeight(inlineElement.id, target, inlineElement.type === 'text');
            setSelection([inlineElement.id], inlineElement.id);
            markDirty();
            renderPropertiesCard();
            return;
        }

        if (applyToolbarColor(target)) {
            markDirty();
            renderCanvas();
            return;
        }

        if (applyScopedColorPreview(target)) {
            return;
        }

        if (applyScopedInput(target)) {
            refreshAfterScopedMutation(target.dataset.scope, target.dataset.path);
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
            refreshAfterScopedMutation(target.dataset.scope, target.dataset.path);
        }
    });

    document.addEventListener('click', function (event) {
        var colorClearTarget = event.target.closest('[data-color-clear]');
        if (state.uiState.addMenuOpen && !event.target.closest('[data-add-menu-root]')) {
            state.uiState.addMenuOpen = false;
            renderBlockCard();
        }

        if (colorClearTarget) {
            event.preventDefault();
            if (applyScopedValue(colorClearTarget.dataset.scope, colorClearTarget.dataset.path, '')) {
                refreshAfterScopedMutation(colorClearTarget.dataset.scope, colorClearTarget.dataset.path);
            }
            return;
        }

        var target = event.target.closest('[data-media-picker-action]');
        var index;

        if (!target) {
            return;
        }

        if (target.dataset.mediaPickerAction === 'clear-current') {
            event.preventDefault();
            if (applyScopedValue(state.mediaPicker.scope, state.mediaPicker.path, '')) {
                refreshAfterScopedMutation(state.mediaPicker.scope, state.mediaPicker.path);
            }
            closeSystemModal();
            return;
        }

        if (target.dataset.mediaPickerAction === 'upload') {
            event.preventDefault();
            triggerImageUpload();
            return;
        }

        if (target.dataset.mediaPickerAction === 'select') {
            event.preventDefault();
            index = Number(target.dataset.mediaIndex || -1);
            if (Number.isFinite(index) && index >= 0) {
                selectImageFromPicker(index);
            }
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

    document.addEventListener('pointerdown', function (event) {
        var scrubNode = event.target.closest('[data-number-scrub]');

        if (!scrubNode || !root.contains(scrubNode)) {
            return;
        }

        beginNumberScrub(event, scrubNode);
    }, true);

    document.addEventListener('pointerdown', function (event) {
        var stageResizeNode = event.target.closest('[data-action="resize-stage-height"]');

        if (!stageResizeNode || !root.contains(stageResizeNode)) {
            return;
        }

        beginStageResize(event);
    }, true);

    document.addEventListener('mousedown', function (event) {
        var scrubNode = event.target.closest('[data-number-scrub]');

        if (!scrubNode || !root.contains(scrubNode)) {
            return;
        }

        beginNumberScrub(event, scrubNode);
    }, true);

    document.addEventListener('mousedown', function (event) {
        var stageResizeNode = event.target.closest('[data-action="resize-stage-height"]');

        if (!stageResizeNode || !root.contains(stageResizeNode)) {
            return;
        }

        beginStageResize(event);
    }, true);

    root.addEventListener('pointerdown', function (event) {
        var resizeNode = event.target.closest('[data-action="resize-element"]');
        var rotateNode = event.target.closest('[data-action="rotate-element"]');
        var stageResizeNode = event.target.closest('[data-action="resize-stage-height"]');
        var wrapper = event.target.closest('.nbde-el');
        var inlineTextNode = event.target.closest('[data-inline-edit="text"]');
        var stageViewport = event.target.closest('#nbd-stage-viewport');
        var worldPoint;
        var hitElement;

        if (!stageViewport) {
            return;
        }

        updatePointerTelemetry(event);

        if (event.button === 1 || (state.interactionState.spacePressed && event.button === 0)) {
            event.preventDefault();
            beginPan(event);
            return;
        }

        worldPoint = getWorldPointFromEvent(event);

        if (stageResizeNode) {
            beginStageResize(event);
            return;
        }

        if (rotateNode && wrapper) {
            beginRotate(wrapper.dataset.elementId || '', event, worldPoint);
            return;
        }

        if (resizeNode && wrapper) {
            beginResize(wrapper.dataset.elementId || '', resizeNode.dataset.handle || '', event, worldPoint);
            return;
        }

        if (inlineTextNode && wrapper) {
            hitElement = getElementById(wrapper.dataset.elementId || '');

            if (hitElement && !hitElement.locked && isEditableType(hitElement.type) && isSelected(hitElement.id) && state.uiState.editingTextId !== hitElement.id) {
                beginTextIntent(hitElement.id, event, worldPoint);
                return;
            }
        }

        if (event.target.closest('[data-inline-edit="text"]') && state.uiState.editingTextId) {
            return;
        }

        hitElement = worldPoint ? hitTestWorldPoint(worldPoint, currentBreakpoint()) : null;

        if (!hitElement) {
            clearSelection();
            renderAll();
            return;
        }

        if (hitElement.locked) {
            setSelection([hitElement.id], hitElement.id);
            revealSelectionInViewport(false);
            renderAll();
            return;
        }

        if (event.shiftKey) {
            toggleSelection(hitElement.id);
            renderAll();
            return;
        }

        setSelection([hitElement.id], hitElement.id);
        beginDrag(hitElement.id, event, worldPoint);
    });

    document.addEventListener('pointermove', function (event) {
        updatePointerTelemetry(event);
        handleNumberScrubMove(event);
        handlePanMove(event);
        handleStageResizeMove(event);
        handleTextIntentMove(event);
        handleRotateMove(event);
        handleResizeMove(event);
        handleDragMove(event);
    });
    document.addEventListener('mousemove', function (event) {
        handleNumberScrubMove(event);
        handleStageResizeMove(event);
    });
    document.addEventListener('pointerup', finishInteraction);
    document.addEventListener('pointercancel', finishInteraction);
    document.addEventListener('mouseup', finishInteraction);

    root.addEventListener('wheel', function (event) {
        var stageViewport = event.target.closest('#nbd-stage-viewport');
        var screenPoint;
        var direction;

        if (!stageViewport) {
            return;
        }

        event.preventDefault();

        if (event.ctrlKey || event.metaKey) {
            screenPoint = getScreenPointFromEvent(event);
            direction = event.deltaY < 0 ? 1.1 : (1 / 1.1);
            zoomTo(screenPoint, Number(state.scene.viewport.zoom || 1) * direction);
            return;
        }

        applyViewportScroll(event.shiftKey && !event.deltaX ? event.deltaY : event.deltaX, event.shiftKey && !event.deltaX ? 0 : event.deltaY);
    }, { passive: false });

    if (nodes.saveButton) {
        nodes.saveButton.addEventListener('click', function () {
            saveContract({ mode: 'manual' });
        });
    }

    window.addEventListener('keydown', function (event) {
        var active = document.activeElement;
        var tagName = active && active.tagName ? active.tagName.toLowerCase() : '';
        var editable = !!(active && active.isContentEditable);

        if (String(event.key || '') === ' ') {
            state.interactionState.spacePressed = true;
        }

        if ((event.ctrlKey || event.metaKey) && String(event.key || '').toLowerCase() === 's') {
            event.preventDefault();
            saveContract({ mode: 'manual' });
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
            return;
        }

        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].indexOf(String(event.key || '')) !== -1) {
            var step = event.shiftKey ? 10 : 1;
            var deltaX = 0;
            var deltaY = 0;

            if (editable || tagName === 'input' || tagName === 'textarea' || tagName === 'select') {
                return;
            }

            if (event.key === 'ArrowLeft') {
                deltaX = -step;
            } else if (event.key === 'ArrowRight') {
                deltaX = step;
            } else if (event.key === 'ArrowUp') {
                deltaY = -step;
            } else if (event.key === 'ArrowDown') {
                deltaY = step;
            }

            if (deltaX || deltaY) {
                event.preventDefault();
                nudgeSelection(deltaX, deltaY);
            }
        }
    });

    window.addEventListener('keyup', function (event) {
        if (String(event.key || '') === ' ') {
            state.interactionState.spacePressed = false;
        }
    });

    window.addEventListener('beforeunload', function (event) {
        if (!state.uiState.isDirty && !state.uiState.isSaving) {
            return;
        }

        triggerKeepaliveSave();
        event.preventDefault();
        event.returnValue = '';
        return '';
    });

    Array.prototype.forEach.call(root.querySelectorAll('[data-breakpoint]'), function (button) {
        button.addEventListener('click', function () {
            state.uiState.activeBreakpoint = button.dataset.breakpoint || 'desktop';
            state.scene.viewport.zoom = 1;
            state.scene.viewport.offsetX = 0;
            state.scene.viewport.offsetY = 0;
            revealSelectionInViewport(true);
            clearGuides();
            renderAll();
        });
    });

    setStageCardExpanded(false);
    setSectionCardExpanded(false);
    setLayersCardExpanded(false);
    attachDebugApi();
    loadState();
})();