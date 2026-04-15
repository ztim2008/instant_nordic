(function () {
    var root = document.querySelector('[data-nordic-schema-builder]');
    if (!root) {
        return;
    }

    var builderState = window.NORDIC_BUILDER_STATE || {};
    var availableCtypes = Array.isArray(builderState.ctypes) ? builderState.ctypes : [];
    var page = builderState.page || {};
    var draftSchema = normalizeSchema(builderState.schema_state || {});
    var publishedSchema = normalizeSchema(builderState.published_schema_state || {});
    var bindingState = normalizeBinding(page.binding || {}, String(page.uri_raw || '/'));
    var bindings = normalizeBindings(builderState.bindings || [], String(page.uri_raw || '/'));
    var sectionList = root.querySelector('[data-schema-sections]');
    var bindingsList = root.querySelector('[data-schema-bindings-list]');
    var canvas = root.querySelector('[data-schema-canvas]');
    var statusNode = root.querySelector('[data-schema-status]');
    var countNode = root.querySelector('[data-schema-count]');
    var bindingsCountNode = root.querySelector('[data-schema-bindings-count]');
    var publishedCountNode = root.querySelector('[data-schema-published-count]');
    var emptyNode = root.querySelector('[data-schema-empty]');
    var inspector = root.querySelector('[data-schema-inspector]');
    var selectionKindNode = root.querySelector('[data-schema-selection-kind]');
    var pageSwitcher = root.querySelector('[data-schema-page-switcher]');
    var bindingSummaryNode = root.querySelector('[data-schema-binding-summary]');
    var bindingNoteNode = root.querySelector('[data-schema-binding-note]');
    var bindingModeTabs = root.querySelectorAll('[data-schema-binding-mode]');
    var blockTypeNode = root.querySelector('[data-schema-block-type]');
    var currentSelection = {
        kind: '',
        sectionId: '',
        blockId: ''
    };
    var counters = {
        section: 0,
        block: 0
    };

    if (!draftSchema.sections.length) {
        draftSchema.sections.push(createStarterSection());
    }

    ensureSelection();
    bindEvents();
    render();

    function bindEvents() {
        root.addEventListener('click', function (event) {
            var bindingModeNode = event.target.closest('[data-schema-binding-mode]');
            if (bindingModeNode) {
                applyBindingFieldValue('route_type', bindingModeNode.getAttribute('data-schema-binding-mode') || 'exact');
                render();
                return;
            }

            var actionNode = event.target.closest('[data-schema-action]');
            if (actionNode) {
                handleAction(actionNode);
                return;
            }

            var selectionNode = event.target.closest('[data-schema-select]');
            if (selectionNode) {
                selectNode(selectionNode);
            }
        });

        root.addEventListener('input', function (event) {
            var bindingField = event.target.getAttribute('data-schema-binding-field');
            if (bindingField) {
                applyBindingFieldValue(bindingField, event.target.value);
                render();
                return;
            }

            var field = event.target.getAttribute('data-schema-field');
            if (!field) {
                return;
            }

            applyFieldValue(field, event.target.value);
            render();
        });

        root.addEventListener('change', function (event) {
            var bindingField = event.target.getAttribute('data-schema-binding-field');
            if (bindingField) {
                applyBindingFieldValue(bindingField, event.target.value);
                render();
                return;
            }

            var field = event.target.getAttribute('data-schema-field');
            if (field) {
                applyFieldValue(field, event.target.value);
                render();
                return;
            }

            if (event.target === pageSwitcher && event.target.value) {
                window.location.href = event.target.value;
            }
        });
    }

    function handleAction(node) {
        var action = node.getAttribute('data-schema-action');
        var sectionId = node.getAttribute('data-section-id') || '';
        var columnId = node.getAttribute('data-column-id') || '';

        if (action === 'open-binding') {
            openBinding(node.getAttribute('data-binding-page-key') || '');
            return;
        }

        if (action === 'delete-binding') {
            deleteBinding(node.getAttribute('data-binding-page-key') || '');
            return;
        }

        if (action === 'add-section') {
            var section = createStarterSection();
            draftSchema.sections.push(section);
            currentSelection = { kind: 'section', sectionId: section.id, blockId: '' };
            setStatus('Добавлена стартовая секция.');
            render();
            return;
        }

        if (action === 'save') {
            sendSchema(builderState.state_url, 'Черновик сохранен.');
            return;
        }

        if (action === 'publish') {
            sendSchema(builderState.publish_url, 'Опубликованная схема обновлена.', true);
            return;
        }

        if (action === 'add-text-section') {
            addTextBlock(sectionId, columnId || getFirstColumnId(sectionId));
            return;
        }

        if (action === 'add-widget-section') {
            addWidgetBlock(sectionId, columnId || getFirstColumnId(sectionId), node.getAttribute('data-widget-kind') || 'text');
            return;
        }

        if (action === 'add-text-selected') {
            var selectedSectionId = sectionId || currentSelection.sectionId;
            if (selectedSectionId) {
                addTextBlock(selectedSectionId, getFirstColumnId(selectedSectionId));
            }
            return;
        }

        if (action === 'add-widget-selected') {
            var targetSectionId = sectionId || currentSelection.sectionId || (draftSchema.sections[0] && draftSchema.sections[0].id) || '';
            if (targetSectionId) {
                addWidgetBlock(targetSectionId, getFirstColumnId(targetSectionId), node.getAttribute('data-widget-kind') || 'text');
            } else {
                setStatus('Сначала добавьте секцию.');
            }
            return;
        }

        if (action === 'delete-selected') {
            deleteSelection();
        }
    }

    function selectNode(node) {
        var kind = node.getAttribute('data-kind') || '';
        var sectionId = node.getAttribute('data-section-id') || '';
        var blockId = node.getAttribute('data-block-id') || '';

        if (kind === 'section' && sectionId) {
            currentSelection = { kind: 'section', sectionId: sectionId, blockId: '' };
            render();
        }

        if (kind === 'block' && sectionId && blockId) {
            currentSelection = { kind: 'block', sectionId: sectionId, blockId: blockId };
            render();
        }
    }

    function render() {
        ensureSelection();
        renderBindingControls();
        renderBindingsList();
        renderSectionList();
        renderCanvas();
        renderInspector();
        updateCounters();
    }

    function renderBindingControls() {
        setBindingFieldValue('route_type', bindingState.route_type);
        setBindingFieldValue('route_pattern', bindingState.route_pattern);

        Array.prototype.forEach.call(bindingModeTabs, function (node) {
            node.classList.toggle('is-active', node.getAttribute('data-schema-binding-mode') === bindingState.route_type);
        });

        var routePatternField = root.querySelector('[data-schema-binding-field="route_pattern"]');
        if (routePatternField) {
            routePatternField.readOnly = bindingState.route_type === 'global';
            routePatternField.placeholder = buildBindingPatternPlaceholder(bindingState.route_type);
        }

        if (bindingSummaryNode) {
            bindingSummaryNode.textContent = buildBindingSummary(bindingState);
        }

        if (bindingNoteNode) {
            bindingNoteNode.textContent = buildBindingNote(bindingState);
        }
    }

    function renderBindingsList() {
        if (bindingsCountNode) {
            bindingsCountNode.textContent = String(bindings.length);
        }

        if (!bindingsList) {
            return;
        }

        if (!bindings.length) {
            bindingsList.innerHTML = '<div class="nb-schema-builder__empty">Сохраненных правил пока нет.</div>';
            return;
        }

        var activeKey = String(bindingState.original_page_key || bindingState.page_key || '');
        var groups = [
            { key: 'exact', title: 'Точный URL' },
            { key: 'prefix', title: 'Разделы' },
            { key: 'global', title: 'Все страницы' }
        ];

        bindingsList.innerHTML = groups.map(function (group) {
            var items = bindings.filter(function (binding) {
                return binding.route_type === group.key;
            });

            if (!items.length) {
                return '';
            }

            return [
                '<div class="nb-schema-builder__binding-group">',
                '<div class="nb-schema-builder__binding-group-title">' + escapeHtml(group.title) + '</div>',
                items.map(function (binding) {
                    return renderBindingCard(binding, activeKey);
                }).join(''),
                '</div>'
            ].join('');
        }).join('') || '<div class="nb-schema-builder__empty">Сохраненных правил пока нет.</div>';
    }

    function renderBindingCard(binding, activeKey) {
        var isActive = activeKey !== '' && binding.page_key === activeKey;
        var statusBadges = [];

        if (binding.has_draft) {
            statusBadges.push('<span>черновик</span>');
        }

        if (binding.has_published) {
            statusBadges.push('<span>опубликовано</span>');
        }

        if (!statusBadges.length) {
            statusBadges.push('<span>пусто</span>');
        }

        return [
            '<article class="nb-schema-builder__binding-card' + (isActive ? ' is-active' : '') + '">',
            '<div class="nb-schema-builder__binding-head">',
            '<strong>' + escapeHtml(buildBindingSummary(binding)) + '</strong>',
            '<span class="nb-schema-builder__binding-badge">' + escapeHtml(buildBindingTypeLabel(binding.route_type)) + '</span>',
            '</div>',
            '<div class="nb-schema-builder__binding-route">' + escapeHtml(buildBindingRouteLabel(binding)) + '</div>',
            '<div class="nb-schema-builder__binding-status">' + statusBadges.join('') + '</div>',
            '<div class="nb-schema-builder__binding-actions">',
            '<button type="button" class="nb-schema-builder__binding-button" data-schema-action="open-binding" data-binding-page-key="' + escapeHtml(binding.page_key) + '">' + (isActive ? 'Открыт' : 'Открыть') + '</button>',
            '<button type="button" class="nb-schema-builder__binding-button nb-schema-builder__binding-button--danger" data-schema-action="delete-binding" data-binding-page-key="' + escapeHtml(binding.page_key) + '">Удалить</button>',
            '</div>',
            '</article>'
        ].join('');
    }

    function renderSectionList() {
        if (!sectionList) {
            return;
        }

        if (!draftSchema.sections.length) {
            sectionList.innerHTML = '<div class="nb-schema-builder__empty">Секций пока нет.</div>';
            return;
        }

        sectionList.innerHTML = draftSchema.sections.map(function (section, index) {
            var isActive = currentSelection.sectionId === section.id;

            return [
                '<button type="button" class="nb-schema-builder__section-item' + (isActive ? ' is-active' : '') + '" data-schema-select data-kind="section" data-section-id="' + escapeHtml(section.id) + '">',
                '<span>',
                '<strong>' + escapeHtml(section.title || ('Секция ' + (index + 1))) + '</strong>',
                '<span>' + section.columns.length + ' колонок · ' + section.blocks.length + ' блоков</span>',
                '</span>',
                '<span class="nb-schema-builder__section-count">#' + (index + 1) + '</span>',
                '</button>'
            ].join('');
        }).join('');
    }

    function renderCanvas() {
        if (!canvas) {
            return;
        }

        if (!draftSchema.sections.length) {
            canvas.innerHTML = '<div class="nb-schema-builder__canvas-empty">Схема еще не начата. Нажмите «Стартовая секция».</div>';
            return;
        }

        canvas.innerHTML = '<div class="nordic-section-page">' + draftSchema.sections.map(renderSection).join('') + '</div>';
    }

    function renderSection(section, index) {
        var style = buildSectionStyle(section);
        var sectionTitle = section.title || ('Секция ' + (index + 1));
        var gridStyle = 'grid-template-columns:' + section.columns.map(function (column) {
            return Math.max(1, parseInt(column.width, 10) || 1) + 'fr';
        }).join(' ') + ';';
        var isSelected = currentSelection.kind === 'section' && currentSelection.sectionId === section.id;

        return [
            '<section class="nordic-section' + (isSelected ? ' is-selected' : '') + '" style="' + style + '" data-schema-select data-kind="section" data-section-id="' + escapeHtml(section.id) + '">',
            '<div class="nb-schema-builder__section-bar">',
            '<div class="nb-schema-builder__section-title">',
            '<strong>' + escapeHtml(sectionTitle) + '</strong>',
            '<span>' + section.columns.length + ' колонок · ' + section.blocks.length + ' блоков</span>',
            '</div>',
            '<div class="nb-schema-builder__section-tools">',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-text-selected" data-section-id="' + escapeHtml(section.id) + '">Текст</button>',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-widget-selected" data-section-id="' + escapeHtml(section.id) + '" data-widget-kind="cta">CTA</button>',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-widget-selected" data-section-id="' + escapeHtml(section.id) + '" data-widget-kind="news_grid">Материалы</button>',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-widget-selected" data-section-id="' + escapeHtml(section.id) + '" data-widget-kind="hero">Hero</button>',
            '</div>',
            '</div>',
            '<div class="nordic-section__grid" style="' + gridStyle + '">',
            section.columns.map(function (column) {
                return renderColumn(section, column);
            }).join(''),
            '</div>',
            '</section>'
        ].join('');
    }

    function renderColumn(section, column) {
        var blocks = section.blocks.filter(function (block) {
            return block.column_id === column.id;
        });

        return [
            '<div class="nb-schema-builder__column">',
            '<div class="nb-schema-builder__column-head">',
            '<span>' + escapeHtml(column.id) + ' · ' + escapeHtml(String(column.width) + '/12') + '</span>',
            '<div class="nb-schema-builder__column-tools">',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-text-section" data-section-id="' + escapeHtml(section.id) + '" data-column-id="' + escapeHtml(column.id) + '">Текст</button>',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-widget-section" data-section-id="' + escapeHtml(section.id) + '" data-column-id="' + escapeHtml(column.id) + '" data-widget-kind="cta">CTA</button>',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-widget-section" data-section-id="' + escapeHtml(section.id) + '" data-column-id="' + escapeHtml(column.id) + '" data-widget-kind="news_grid">Материалы</button>',
            '<button type="button" class="nb-schema-builder__tool" data-schema-action="add-widget-section" data-section-id="' + escapeHtml(section.id) + '" data-column-id="' + escapeHtml(column.id) + '" data-widget-kind="hero">Hero</button>',
            '</div>',
            '</div>',
            '<div class="nordic-section__column-stack">',
            (blocks.length ? blocks.map(function (block) {
                return renderBlock(section, block);
            }).join('') : '<div class="nb-schema-builder__empty">Колонка пустая.</div>'),
            '</div>',
            '</div>'
        ].join('');
    }

    function renderBlock(section, block) {
        var isSelected = currentSelection.kind === 'block' && currentSelection.blockId === block.id;
        var style = buildBlockStyle(block);

        return [
            '<article class="nordic-section__block nordic-section__block--' + escapeHtml(block.widget) + (isSelected ? ' is-selected' : '') + '" style="' + style + '" data-schema-select data-kind="block" data-section-id="' + escapeHtml(section.id) + '" data-block-id="' + escapeHtml(block.id) + '">',
            '<div class="nb-schema-builder__block-badge">' + escapeHtml(buildWidgetLabel(block.widget)) + '</div>',
            renderBlockInner(block),
            '</article>'
        ].join('');
    }

    function renderBlockInner(block) {
        if (block.widget === 'cta') {
            return [
                '<div class="nordic-section__cta">',
                (block.props.eyebrow ? '<div class="nordic-section__cta-eyebrow">' + escapeHtml(block.props.eyebrow) + '</div>' : ''),
                '<div class="nordic-section__cta-title">' + escapeHtml(block.props.title || 'Сильный оффер для следующего шага') + '</div>',
                '<div class="nordic-section__cta-text">' + escapeHtml(block.props.text || 'Коротко объясните, что пользователь получит после клика по кнопке.') + '</div>',
                (block.props.button_label ? '<span class="nordic-section__cta-button">' + escapeHtml(block.props.button_label) + '</span>' : ''),
                '</div>'
            ].join('');
        }

        if (block.widget === 'news_grid') {
            var ctypeLabel = block.props.ctype ? (availableCtypes.find(function(c){ return c.name === block.props.ctype; }) || {title: block.props.ctype}).title : 'не выбрано';
            return [
                '<div class="nordic-section__widget-preview">',
                '<div class="nordic-section__widget-preview-label">Материалы: ' + escapeHtml(ctypeLabel) + ' · ' + escapeHtml(String(block.props.limit || 6)) + ' шт. · ' + escapeHtml(String(block.props.columns || 3)) + ' колонки</div>',
                '<div class="nordic-section__widget-preview-hint">Настройте тип контента в инспекторе. На живой странице покажутся реальные карточки.</div>',
                '</div>'
            ].join('');
        }

        if (block.widget === 'hero') {
            return [
                '<div class="nordic-section__hero-preview" style="min-height:' + escapeHtml(String(block.props.hero_height || 400)) + 'px">',
                '<div class="nordic-section__cta-eyebrow">' + escapeHtml(block.props.eyebrow || '') + '</div>',
                '<div class="nordic-section__cta-title">' + escapeHtml(block.props.title || 'Hero-заголовок') + '</div>',
                '<div class="nordic-section__cta-text">' + escapeHtml(block.props.text || '') + '</div>',
                (block.props.button_label ? '<span class="nordic-section__cta-button">' + escapeHtml(block.props.button_label) + '</span>' : ''),
                '</div>'
            ].join('');
        }

        return '<div class="nordic-section__block-content">' + escapeHtml(block.props.text || 'Текстовый блок') + '</div>';
    }

    function renderInspector() {
        var sectionGroup = root.querySelector('[data-schema-group="section"]');
        var blockGroup = root.querySelector('[data-schema-group="block"]');

        if (!inspector || !emptyNode || !sectionGroup || !blockGroup) {
            return;
        }

        emptyNode.hidden = !currentSelection.kind;
        inspector.hidden = !currentSelection.kind;
        selectionKindNode.textContent = buildSelectionKindLabel(currentSelection.kind);
        sectionGroup.hidden = currentSelection.kind !== 'section';
        blockGroup.hidden = currentSelection.kind !== 'block';

        if (currentSelection.kind === 'section') {
            var section = getSelectedSection();
            if (!section) {
                return;
            }

            setFieldValue('section.title', section.title || '');
            setFieldValue('section.style.background', section.style.background || '');
            setFieldValue('section.style.padding_top', section.style.padding_top);
            setFieldValue('section.style.padding_bottom', section.style.padding_bottom);
            setValueNode('section.style.padding_top', section.style.padding_top + ' px');
            setValueNode('section.style.padding_bottom', section.style.padding_bottom + ' px');
            return;
        }

        if (currentSelection.kind === 'block') {
            var block = getSelectedBlock();
            var currentSection = getSelectedSection();
            if (!block || !currentSection) {
                return;
            }

            if (blockTypeNode) {
                blockTypeNode.textContent = buildWidgetLabel(block.widget);
            }

            toggleBlockInspectorGroups(block.widget);
            setFieldValue('block.props.text', block.props.text || '');
            setFieldValue('block.props.eyebrow', block.props.eyebrow || '');
            setFieldValue('block.props.title', block.props.title || '');
            setFieldValue('block.props.button_label', block.props.button_label || '');
            setFieldValue('block.props.button_url', block.props.button_url || '');
            setFieldValue('block.style.font_size', block.style.font_size);
            setFieldValue('block.style.title_size', block.style.title_size);
            setFieldValue('block.style.color', block.style.color || '');
            setFieldValue('block.style.text_align', block.style.text_align || 'left');
            setFieldValue('block.style.background', block.style.background || '');
            setFieldValue('block.style.padding', block.style.padding);
            setFieldValue('block.style.accent_color', block.style.accent_color || '');
            setValueNode('block.style.font_size', block.style.font_size + ' px');
            setValueNode('block.style.title_size', block.style.title_size + ' px');
            setValueNode('block.style.padding', block.style.padding + ' px');
            populateColumnSelect(currentSection, block.column_id);
            if (block.widget === 'news_grid') {
                populateCtypeSelect(block.props.ctype || '');
                setFieldValue('block.props.limit', block.props.limit || 6);
                setFieldValue('block.props.fields', block.props.fields || 'title,image,date_pub');
                setFieldValue('block.props.columns', block.props.columns || 3);
            }
            if (block.widget === 'hero') {
                setFieldValue('block.props.hero_height', block.props.hero_height || 400);
            }
        }
    }

    function toggleBlockInspectorGroups(widget) {
        Array.prototype.forEach.call(root.querySelectorAll('[data-schema-widget-group]'), function (node) {
            node.hidden = node.getAttribute('data-schema-widget-group') !== widget;
        });
    }

    function populateCtypeSelect(selectedCtype) {
        var select = root.querySelector('[data-schema-field="block.props.ctype"]');
        if (!select) { return; }

        var options = availableCtypes.map(function (ct) {
            var sel = ct.name === selectedCtype ? ' selected' : '';
            return '<option value="' + escapeHtml(ct.name) + '"' + sel + '>' + escapeHtml(ct.title) + '</option>';
        });
        options.unshift('<option value=""' + (selectedCtype === '' ? ' selected' : '') + '>— выберите тип контента —</option>');
        select.innerHTML = options.join('');
    }

    function populateColumnSelect(section, selectedColumnId) {
        var select = root.querySelector('[data-schema-field="block.column_id"]');
        if (!select || !section) {
            return;
        }

        select.innerHTML = section.columns.map(function (column) {
            var selected = column.id === selectedColumnId ? ' selected' : '';
            return '<option value="' + escapeHtml(column.id) + '"' + selected + '>' + escapeHtml(column.id + ' (' + column.width + '/12)') + '</option>';
        }).join('');
    }

    function updateCounters() {
        if (countNode) {
            countNode.textContent = String(draftSchema.sections.length);
        }

        if (publishedCountNode) {
            publishedCountNode.textContent = String(publishedSchema.sections.length);
        }
    }

    function addTextBlock(sectionId, columnId) {
        addWidgetBlock(sectionId, columnId, 'text');
    }

    function addWidgetBlock(sectionId, columnId, widgetKind) {
        var section = findSection(sectionId);
        if (!section) {
            setStatus('Сначала добавьте или выберите секцию.');
            return;
        }

        var normalizedWidget = normalizeWidgetType(widgetKind);
        var block = createBlock(normalizedWidget, columnId || getFirstColumnId(sectionId));
        section.blocks.push(block);
        currentSelection = { kind: 'block', sectionId: section.id, blockId: block.id };
        setStatus(normalizedWidget === 'cta' ? 'Добавлен блок CTA.' : 'Добавлен текстовый блок.');
        render();
    }

    function deleteSelection() {
        if (currentSelection.kind === 'section') {
            draftSchema.sections = draftSchema.sections.filter(function (section) {
                return section.id !== currentSelection.sectionId;
            });
            ensureSelection();
            setStatus('Секция удалена.');
            render();
            return;
        }

        if (currentSelection.kind === 'block') {
            var section = findSection(currentSelection.sectionId);
            if (!section) {
                return;
            }

            section.blocks = section.blocks.filter(function (block) {
                return block.id !== currentSelection.blockId;
            });
            currentSelection = { kind: 'section', sectionId: section.id, blockId: '' };
            setStatus('Блок удален.');
            render();
        }
    }

    function applyFieldValue(field, value) {
        var section = getSelectedSection();
        var block = getSelectedBlock();

        if (field.indexOf('section.') === 0 && section) {
            if (field === 'section.title') {
                section.title = String(value || '');
            }
            if (field === 'section.style.background') {
                section.style.background = String(value || '');
            }
            if (field === 'section.style.padding_top') {
                section.style.padding_top = clampNumber(value, 0, 240, 48);
            }
            if (field === 'section.style.padding_bottom') {
                section.style.padding_bottom = clampNumber(value, 0, 240, 48);
            }
            return;
        }

        if (field.indexOf('block.') === 0 && block) {
            if (field === 'block.props.text') {
                block.props.text = String(value || '');
            }
            if (field === 'block.props.eyebrow') {
                block.props.eyebrow = String(value || '');
            }
            if (field === 'block.props.title') {
                block.props.title = String(value || '');
            }
            if (field === 'block.props.button_label') {
                block.props.button_label = String(value || '');
            }
            if (field === 'block.props.button_url') {
                block.props.button_url = String(value || '');
            }
            if (field === 'block.column_id') {
                block.column_id = String(value || getFirstColumnId(currentSelection.sectionId));
            }
            if (field === 'block.style.font_size') {
                block.style.font_size = clampNumber(value, 10, 120, 18);
            }
            if (field === 'block.style.title_size') {
                block.style.title_size = clampNumber(value, 20, 96, 40);
            }
            if (field === 'block.style.color') {
                block.style.color = String(value || '#111111');
            }
            if (field === 'block.style.text_align') {
                block.style.text_align = normalizeTextAlign(value);
            }
            if (field === 'block.style.background') {
                block.style.background = String(value || '');
            }
            if (field === 'block.style.padding') {
                block.style.padding = clampNumber(value, 0, 240, 0);
            }
            if (field === 'block.style.accent_color') {
                block.style.accent_color = String(value || '#155e63');
            }
            if (field === 'block.props.ctype') {
                block.props.ctype = String(value || '');
            }
            if (field === 'block.props.limit') {
                block.props.limit = Math.max(1, Math.min(24, parseInt(value, 10) || 6));
            }
            if (field === 'block.props.fields') {
                block.props.fields = String(value || 'title,image,date_pub');
            }
            if (field === 'block.props.columns') {
                block.props.columns = Math.max(1, Math.min(6, parseInt(value, 10) || 3));
            }
            if (field === 'block.props.hero_height') {
                block.props.hero_height = Math.max(200, Math.min(800, parseInt(value, 10) || 400));
            }
        }
    }

    function applyBindingFieldValue(field, value) {
        if (field === 'route_type') {
            var previousRouteType = bindingState.route_type;
            var nextRouteType = normalizeBindingType(value);
            var patternSource = bindingState.route_pattern;

            if (previousRouteType === 'global' && nextRouteType === 'prefix' && String(patternSource || '/') === '/') {
                patternSource = String(page.uri_raw || '/');
            }

            bindingState.route_type = nextRouteType;
            bindingState.scope = bindingState.route_type === 'global' ? 'site' : 'page';
            bindingState.route_pattern = normalizeBindingPattern(bindingState.route_type, patternSource, String(page.uri_raw || '/'));
            return;
        }

        if (field === 'route_pattern') {
            bindingState.route_pattern = normalizeBindingPattern(bindingState.route_type, value, String(page.uri_raw || '/'));
            if (bindingState.route_type === 'prefix' && bindingState.route_pattern === '/') {
                bindingState.route_type = 'global';
                bindingState.scope = 'site';
            }
        }
    }

    function sendSchema(url, successFallback, updatePublished) {
        if (!url) {
            setStatus('URL для сохранения не настроен.');
            return;
        }

        setStatus('Отправляю схему на сервер...');

        var previousOriginalPageKey = String(bindingState.original_page_key || bindingState.page_key || '');

        fetch(String(url), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeRequestBody({
                csrf_token: String(builderState.csrf_token || ''),
                template: String(page.template || ''),
                uri: String(page.uri_raw || '/'),
                original_page_key: String(bindingState.original_page_key || ''),
                route_type: String(bindingState.route_type || 'exact'),
                route_pattern: String(bindingState.route_pattern || String(page.uri_raw || '/')),
                scope: String(bindingState.scope || 'page'),
                schema_json: JSON.stringify(draftSchema)
            })
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (payload && payload.error) {
                    setStatus(String(payload.message || 'Сервер не сохранил схему.'));
                    return;
                }

                if (updatePublished) {
                    publishedSchema = clone(draftSchema);
                }

                if (payload && payload.binding) {
                    bindingState = normalizeBinding(payload.binding, String(page.uri_raw || '/'));
                }

                if (payload && Array.isArray(payload.bindings)) {
                    bindings = normalizeBindings(payload.bindings, String(page.uri_raw || '/'));

                    if (bindingState && previousOriginalPageKey !== '' && previousOriginalPageKey !== String(bindingState.page_key || '')) {
                        var stillHasPreviousBinding = bindings.some(function (entry) {
                            return String(entry.page_key || '') === previousOriginalPageKey;
                        });

                        if (stillHasPreviousBinding) {
                            bindingState.original_page_key = previousOriginalPageKey;
                        }
                    }
                }

                setStatus(String((payload && payload.message) || successFallback));
                render();
            })
            .catch(function () {
                setStatus('Ошибка соединения при сохранении схемы.');
            });
    }

    function openBinding(pageKey) {
        var openUrl = buildBindingOpenUrl(pageKey);
        if (openUrl) {
            window.location.href = openUrl;
        }
    }

    function deleteBinding(pageKey) {
        if (!pageKey || !builderState.binding_delete_url) {
            return;
        }

        if (window.confirm && !window.confirm('Удалить binding целиком? Draft и published записи будут удалены.')) {
            return;
        }

        setStatus('Удаляю binding...');

        fetch(String(builderState.binding_delete_url), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeRequestBody({
                csrf_token: String(builderState.csrf_token || ''),
                template: String(page.template || ''),
                page_key: String(pageKey)
            })
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (payload && payload.error) {
                    setStatus(String(payload.message || 'Не удалось удалить binding.'));
                    return;
                }

                if (payload && Array.isArray(payload.bindings)) {
                    bindings = normalizeBindings(payload.bindings, String(page.uri_raw || '/'));
                }

                setStatus(String((payload && payload.message) || 'Binding удален.'));

                if (String(bindingState.original_page_key || bindingState.page_key || '') === String(pageKey)) {
                    window.location.href = String(builderState.schema_url || buildBindingBaseUrl());
                    return;
                }

                render();
            })
            .catch(function () {
                setStatus('Ошибка соединения при удалении binding.');
            });
    }

    function ensureSelection() {
        if (!draftSchema.sections.length) {
            currentSelection = { kind: '', sectionId: '', blockId: '' };
            return;
        }

        var section = findSection(currentSelection.sectionId);
        if (!section) {
            currentSelection = { kind: 'section', sectionId: draftSchema.sections[0].id, blockId: '' };
            return;
        }

        if (currentSelection.kind === 'block') {
            var block = findBlock(currentSelection.sectionId, currentSelection.blockId);
            if (!block) {
                currentSelection = { kind: 'section', sectionId: section.id, blockId: '' };
            }
        }

        if (!currentSelection.kind) {
            currentSelection = { kind: 'section', sectionId: section.id, blockId: '' };
        }
    }

    function getSelectedSection() {
        return findSection(currentSelection.sectionId);
    }

    function getSelectedBlock() {
        if (currentSelection.kind !== 'block') {
            return null;
        }

        return findBlock(currentSelection.sectionId, currentSelection.blockId);
    }

    function findSection(sectionId) {
        return draftSchema.sections.find(function (section) {
            return section.id === sectionId;
        }) || null;
    }

    function findBlock(sectionId, blockId) {
        var section = findSection(sectionId);
        if (!section) {
            return null;
        }

        return section.blocks.find(function (block) {
            return block.id === blockId;
        }) || null;
    }

    function getFirstColumnId(sectionId) {
        var section = findSection(sectionId);
        if (!section || !section.columns.length) {
            return 'col_1';
        }

        return section.columns[0].id;
    }

    function createStarterSection() {
        counters.section += 1;

        return normalizeSection({
            id: 'section_' + Date.now().toString(36) + '_' + counters.section,
            title: 'Стартовая секция',
            columns: [
                { id: 'col_1', width: 6 },
                { id: 'col_2', width: 6 }
            ],
            style: {
                padding_top: 72,
                padding_bottom: 72,
                background: '#ffffff'
            },
            blocks: [
                createTextBlock('col_1', 'Сильный заголовок секции'),
                createCtaBlock('col_2')
            ]
        });
    }

    function createBlock(widgetKind, columnId) {
        if (widgetKind === 'cta') { return createCtaBlock(columnId); }
        if (widgetKind === 'news_grid') { return createNewsGridBlock(columnId); }
        if (widgetKind === 'hero') { return createHeroBlock(columnId); }
        return createTextBlock(columnId);
    }

    function createNewsGridBlock(columnId) {
        counters.block += 1;
        return normalizeBlock({
            id: 'block_' + Date.now().toString(36) + '_' + counters.block,
            column_id: columnId || 'col_1',
            widget: 'news_grid',
            props: {
                ctype: availableCtypes.length > 0 ? availableCtypes[0].name : '',
                limit: 6,
                columns: 3,
                fields: 'title,image,date_pub'
            },
            style: {
                font_size: 16,
                title_size: 22,
                color: '#111111',
                text_align: 'left',
                background: '',
                padding: 0,
                accent_color: '#155e63'
            }
        });
    }

    function createHeroBlock(columnId) {
        counters.block += 1;
        return normalizeBlock({
            id: 'block_' + Date.now().toString(36) + '_' + counters.block,
            column_id: columnId || 'col_1',
            widget: 'hero',
            props: {
                eyebrow: 'Добро пожаловать',
                title: 'Большой заголовок вашей страницы',
                text: 'Краткое описание ценности или предложения',
                button_label: 'Подробнее',
                button_url: '',
                hero_height: 400
            },
            style: {
                font_size: 18,
                title_size: 56,
                color: '#ffffff',
                text_align: 'center',
                background: 'linear-gradient(135deg, #155e63 0%, #163038 100%)',
                padding: 60,
                accent_color: '#f4ede0'
            }
        });
    }

    function createTextBlock(columnId, text) {
        counters.block += 1;

        return normalizeBlock({
            id: 'block_' + Date.now().toString(36) + '_' + counters.block,
            column_id: columnId || 'col_1',
            widget: 'text',
            props: {
                text: text || 'Новый текстовый блок'
            },
            style: {
                font_size: 18,
                title_size: 40,
                color: '#111111',
                text_align: 'left',
                background: '',
                padding: 0,
                accent_color: '#155e63'
            }
        });
    }

    function createCtaBlock(columnId) {
        counters.block += 1;

        return normalizeBlock({
            id: 'block_' + Date.now().toString(36) + '_' + counters.block,
            column_id: columnId || 'col_1',
            widget: 'cta',
            props: {
                eyebrow: 'Спецпредложение',
                title: 'Соберите следующий шаг прямо в секции',
                text: 'Добавьте короткое обещание ценности и переведите пользователя к нужному действию без отдельной верстки.',
                button_label: 'Оставить заявку',
                button_url: '/contacts'
            },
            style: {
                font_size: 18,
                title_size: 40,
                color: '#163038',
                text_align: 'left',
                background: 'linear-gradient(135deg, #f4ede0 0%, #f7fbf9 100%)',
                padding: 28,
                accent_color: '#155e63'
            }
        });
    }

    function normalizeSchema(schema) {
        var sections = Array.isArray(schema.sections) ? schema.sections.map(normalizeSection) : [];

        return {
            version: Math.max(1, parseInt(schema.version || 1, 10) || 1),
            sections: sections
        };
    }

    function normalizeBinding(binding, currentUri) {
        var routeType = normalizeBindingType(binding.route_type || 'exact');
        var routePattern = normalizeBindingPattern(routeType, binding.route_pattern || '', currentUri || '/');

        if (routeType === 'prefix' && routePattern === '/') {
            routeType = 'global';
        }

        var scope = routeType === 'global' ? 'site' : String(binding.scope || 'page');
        var pageKey = String(binding.page_key || buildBindingKey(routeType, routeType === 'global' ? '/' : routePattern, scope));

        return {
            page_key: pageKey,
            original_page_key: String(binding.original_page_key || pageKey),
            route_type: routeType,
            route_pattern: routeType === 'global' ? '/' : routePattern,
            scope: scope
        };
    }

    function normalizeBindings(entries, currentUri) {
        if (!Array.isArray(entries)) {
            return [];
        }

        return entries.map(function (entry) {
            var normalized = normalizeBinding(entry || {}, currentUri || '/');

            normalized.has_draft = Boolean(entry && entry.has_draft);
            normalized.has_published = Boolean(entry && entry.has_published);
            normalized.updated_at = String(entry && entry.updated_at || '');

            return normalized;
        });
    }

    function normalizeSection(section) {
        var columns = Array.isArray(section.columns) && section.columns.length ? section.columns.map(normalizeColumn) : [
            normalizeColumn({ id: 'col_1', width: 6 }),
            normalizeColumn({ id: 'col_2', width: 6 })
        ];
        var defaultColumnId = columns[0].id;

        return {
            id: String(section.id || ('section_' + Date.now().toString(36))),
            title: String(section.title || ''),
            columns: columns,
            style: {
                padding_top: clampNumber(section.style && section.style.padding_top, 0, 240, 48),
                padding_bottom: clampNumber(section.style && section.style.padding_bottom, 0, 240, 48),
                background: String(section.style && section.style.background || '')
            },
            blocks: Array.isArray(section.blocks) ? section.blocks.map(function (block) {
                var normalized = normalizeBlock(block);
                if (!columnExists(columns, normalized.column_id)) {
                    normalized.column_id = defaultColumnId;
                }
                return normalized;
            }) : []
        };
    }

    function normalizeColumn(column) {
        return {
            id: String(column.id || 'col_1'),
            width: clampNumber(column.width, 1, 12, 6)
        };
    }

    function normalizeBlock(block) {
        var widget = normalizeWidgetType(block.widget);

        return {
            id: String(block.id || ('block_' + Date.now().toString(36))),
            column_id: String(block.column_id || 'col_1'),
            widget: widget,
            props: normalizeBlockProps(widget, block.props || {}),
            style: {
                font_size: clampNumber(block.style && block.style.font_size, 10, 120, 18),
                title_size: clampNumber(block.style && block.style.title_size, 20, 96, 40),
                color: String(block.style && block.style.color || '#111111'),
                text_align: normalizeTextAlign(block.style && block.style.text_align),
                background: String(block.style && block.style.background || ''),
                padding: clampNumber(block.style && block.style.padding, 0, 240, 0),
                accent_color: String(block.style && block.style.accent_color || '#155e63')
            }
        };
    }

    function normalizeBlockProps(widget, props) {
        if (widget === 'cta') {
            return {
                eyebrow: String(props && props.eyebrow || ''),
                title: String(props && props.title || ''),
                text: String(props && props.text || ''),
                button_label: String(props && props.button_label || ''),
                button_url: String(props && props.button_url || '')
            };
        }

        return {
            text: String(props && props.text || '')
        };
    }

    function columnExists(columns, columnId) {
        return columns.some(function (column) {
            return column.id === columnId;
        });
    }

    function buildSectionStyle(section) {
        var style = [
            '--section-pad-top:' + section.style.padding_top + 'px',
            '--section-pad-bottom:' + section.style.padding_bottom + 'px'
        ];

        if (section.style.background) {
            style.push('background:' + section.style.background);
        }

        return style.join(';');
    }

    function buildBlockStyle(block) {
        var style = [
            '--block-font-size:' + block.style.font_size + 'px',
            '--block-title-size:' + block.style.title_size + 'px',
            '--block-color:' + block.style.color,
            '--block-align:' + block.style.text_align,
            '--block-padding:' + block.style.padding + 'px',
            '--block-accent-color:' + block.style.accent_color
        ];

        if (block.style.background) {
            style.push('background:' + block.style.background);
        }

        return style.join(';');
    }

    function setFieldValue(name, value) {
        Array.prototype.forEach.call(root.querySelectorAll('[data-schema-field="' + name + '"]'), function (node) {
            node.value = String(value == null ? '' : value);
        });
    }

    function setValueNode(name, value) {
        var node = root.querySelector('[data-schema-value="' + name + '"]');
        if (node) {
            node.textContent = String(value == null ? '' : value);
        }
    }

    function setStatus(message) {
        if (statusNode) {
            statusNode.textContent = String(message || '');
        }
    }

    function normalizeTextAlign(value) {
        value = String(value || '').toLowerCase();

        return ['left', 'center', 'right', 'justify'].indexOf(value) !== -1 ? value : 'left';
    }

    function normalizeWidgetType(value) {
        value = String(value || '').toLowerCase();

        return ['text', 'cta', 'news_grid', 'hero'].indexOf(value) !== -1 ? value : 'text';
    }

    function normalizeBindingType(value) {
        value = String(value || '').toLowerCase();

        return ['exact', 'prefix', 'global'].indexOf(value) !== -1 ? value : 'exact';
    }

    function normalizeBindingPattern(routeType, value, currentUri) {
        var normalized = String(value || '').trim();

        if (routeType === 'global') {
            return '/';
        }

        if (!normalized) {
            normalized = String(currentUri || '/');
        }

        normalized = '/' + normalized.replace(/^\/+/, '');
        normalized = normalized.replace(/\/+$/, '');

        return normalized === '' ? '/' : normalized;
    }

    function buildBindingKey(routeType, routePattern, scope) {
        if (routeType === 'exact') {
            return String(routePattern || '/');
        }

        return String(routeType || 'exact') + ':' + String(scope || 'page') + ':' + String(routePattern || '/');
    }

    function buildBindingSummary(binding) {
        if (binding.route_type === 'global') {
            return 'сквозной · весь сайт';
        }

        if (binding.route_type === 'prefix') {
            return 'раздел · ' + (binding.route_pattern === '/' ? 'все URL' : binding.route_pattern + '/*');
        }

        return 'точный · ' + binding.route_pattern;
    }

    function buildBindingRouteLabel(binding) {
        if (binding.route_type === 'global') {
            return 'Сквозное правило для всего сайта в рамках текущего шаблона.';
        }

        if (binding.route_type === 'prefix') {
            return 'Раздел: ' + (binding.route_pattern === '/' ? 'все URL' : binding.route_pattern + '/*');
        }

        return 'URL: ' + String(binding.route_pattern || '/');
    }

    function buildBindingNote(binding) {
        if (binding.route_type === 'global') {
            return 'Режим «Все страницы» публикует одну схему как сквозное правило для всего сайта в рамках текущего шаблона.';
        }

        if (binding.route_type === 'prefix') {
            return 'Режим «Раздел» показывает схему на всех URL, которые начинаются с указанного пути. Более длинный префикс приоритетнее.';
        }

        return 'Режим «Точный URL» показывает схему только на одном адресе. Если точного правила нет, страница возьмет правило раздела или сквозное.';
    }

    function buildBindingPatternPlaceholder(routeType) {
        if (routeType === 'global') {
            return 'Для сквозного режима путь не нужен';
        }

        if (routeType === 'prefix') {
            return '/news';
        }

        return '/news/sport';
    }

    function buildBindingTypeLabel(routeType) {
        if (routeType === 'global') {
            return 'Все страницы';
        }

        if (routeType === 'prefix') {
            return 'Раздел';
        }

        return 'Точный URL';
    }

    function buildSelectionKindLabel(kind) {
        if (kind === 'section') {
            return 'секция';
        }

        if (kind === 'block') {
            return 'блок';
        }

        return 'ничего';
    }

    function buildWidgetLabel(widget) {
        var labels = { 'cta': 'CTA', 'news_grid': 'Материалы', 'hero': 'Hero' };
        return labels[normalizeWidgetType(widget)] || 'Текст';
    }

    function setBindingFieldValue(name, value) {
        var node = root.querySelector('[data-schema-binding-field="' + name + '"]');
        if (node) {
            node.value = String(value == null ? '' : value);
        }
    }

    function buildBindingBaseUrl() {
        if (builderState.schema_url) {
            return String(builderState.schema_url);
        }

        return String(window.location.href || '');
    }

    function buildBindingOpenUrl(pageKey) {
        if (!pageKey) {
            return '';
        }

        try {
            var url = new URL(buildBindingBaseUrl(), window.location.origin);
            url.searchParams.set('binding_key', String(pageKey));
            return url.toString();
        } catch (error) {
            return buildBindingBaseUrl();
        }
    }

    function clampNumber(value, min, max, fallback) {
        var numeric = parseInt(value, 10);
        if (isNaN(numeric)) {
            numeric = fallback;
        }
        if (numeric < min) {
            return min;
        }
        if (numeric > max) {
            return max;
        }
        return numeric;
    }

    function serializeRequestBody(payload) {
        return Object.keys(payload).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(String(payload[key]));
        }).join('&');
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }
}());