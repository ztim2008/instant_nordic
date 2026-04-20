function nbhHasCapability(key) {
    return !!nbhGet(nbhState.server, 'resolved.capabilities.' + key, false);
}

function nbhHasEntity(key) {
    return !!nbhGet(nbhState.server, 'resolved.entities.' + key, false);
}

function nbhPanelVisible(panel) {
    var required = panel.requiresCapabilities || [];
    var i;

    if (panel.tab !== nbhState.activeTab) {
        return false;
    }

    for (i = 0; i < required.length; i++) {
        if (!nbhHasCapability(required[i])) {
            return false;
        }
    }

    required = panel.requiresEntities || [];
    for (i = 0; i < required.length; i++) {
        if (!nbhHasEntity(required[i])) {
            return false;
        }
    }

    if (panel.requiresAnyEntities && panel.requiresAnyEntities.length) {
        var hasAny = panel.requiresAnyEntities.some(function(entityKey) { return nbhHasEntity(entityKey); });
        if (!hasAny) {
            return false;
        }
    }

    return true;
}

function nbhPanelMatchesSelection(panel, entityKey) {
    var selected = entityKey || nbhState.selectedEntity;
    var scope = panel.entityScope || 'block';
    var groups = nbhState.inspector && nbhState.inspector.entityGroups ? nbhState.inspector.entityGroups : {};

    if (!selected || scope === 'block' || scope === 'section') {
        return true;
    }

    if (scope === selected) {
        return true;
    }

    if (groups[scope] && Array.isArray(groups[scope].entities) && groups[scope].entities.indexOf(selected) !== -1) {
        return true;
    }

    return false;
}

function nbhPanelsForTab(entityKey) {
    var panels = (nbhState.inspector && nbhState.inspector.availablePanels ? nbhState.inspector.availablePanels : []).filter(function(panel) {
        return nbhPanelVisible(panel) && nbhPanelMatchesSelection(panel, entityKey);
    });
    var selected = entityKey || nbhState.selectedEntity;

    return panels.sort(function(a, b) {
        var aScore = a.entityScope === selected ? -1 : 0;
        var bScore = b.entityScope === selected ? -1 : 0;
        if (aScore !== bScore) return aScore - bScore;
        return (a.order || 0) - (b.order || 0);
    });
}

function nbhPanelSectionGroups(panels) {
    var groups = {};

    panels.forEach(function(panel) {
        var sectionKey = panel.section || 'general';
        if (!groups[sectionKey]) {
            groups[sectionKey] = {
                key: sectionKey,
                label: nbhHumanSection(sectionKey),
                panels: [],
                order: panel.order || 0
            };
        }

        groups[sectionKey].panels.push(panel);
        if ((panel.order || 0) < groups[sectionKey].order) {
            groups[sectionKey].order = panel.order || 0;
        }
    });

    return Object.keys(groups).map(function(key) { return groups[key]; }).sort(function(a, b) {
        return a.order - b.order;
    });
}

function nbhPreferredAccordionKey(groups) {
    var selected = nbhState.selectedEntity;
    var preferred = groups.find(function(group) {
        return group.panels.some(function(panel) {
            return panel.entityScope === selected;
        });
    });

    if (preferred) {
        return preferred.key;
    }

    return groups.length ? groups[0].key : '';
}

function nbhActiveAccordionKey(groups) {
    var tab = nbhState.activeTab || 'content';
    var current = nbhState.openAccordionByTab[tab] || '';
    var exists = groups.some(function(group) { return group.key === current; });

    if (exists) {
        return current;
    }

    current = nbhPreferredAccordionKey(groups);
    nbhState.openAccordionByTab[tab] = current;
    return current;
}

function nbhRenderAccordionGroup(group, activeKey) {
    var isOpen = group.key === activeKey;
    var panelCountLabel = group.panels.length === 1 ? '1 панель' : (group.panels.length + ' панелей');

    return '<section class="nbh-accordion-group' + (isOpen ? ' is-open' : '') + '" data-section="' + group.key + '">'
        + '<button type="button" class="nbh-accordion-toggle" data-accordion-key="' + group.key + '">'
        + '<span><strong>' + group.label + '</strong><span>' + panelCountLabel + '</span></span>'
        + '<i class="fa fa-chevron-down nbh-accordion-icon"></i>'
        + '</button>'
        + '<div class="nbh-accordion-body">' + group.panels.map(nbhRenderPanel).join('') + '</div>'
        + '</section>';
}

function nbhTabLabel(tabKey) {
    var labels = {
        content: 'Контент',
        design: 'Дизайн',
        layout: 'Макет',
        data: 'Данные'
    };

    return labels[tabKey] || tabKey;
}

function nbhEntityPriorityForActiveTab(entityKey) {
    var panels = nbhPanelsForTab(entityKey);
    var firstPanel = panels.length ? panels[0] : null;
    var score = 0;

    if (!panels.length) {
        return -100000;
    }

    if (nbhState.activeTab === 'content') {
        if (entityKey === 'items' && nbhHasCapability('repeaterContent')) {
            score += 500;
        }

        if (entityKey === 'slide' && nbhHasCapability('hasSlides')) {
            score += 500;
        }

        if (entityKey === 'slides' && nbhHasCapability('hasSlides')) {
            score += 460;
        }

        if (panels.some(function(panel) { return nbhPanelControlKey(panel) === 'repeaterItems' || panel.section === 'repeaters'; })) {
            score += 450;
        }

        if (entityKey === 'title') {
            score += 220;
        }

        if (panels.some(function(panel) { return nbhPanelControlKey(panel) === 'textContent'; })) {
            score += 120;
        }
    }

    if (nbhState.activeTab === 'data') {
        if (entityKey === 'items' && panels.some(function(panel) { return nbhPanelControlKey(panel) === 'dataCollection'; })) {
            score += 260;
        }

        if ((entityKey === 'slide' || entityKey === 'slides') && panels.some(function(panel) {
            var key = nbhPanelControlKey(panel);
            return key === 'sliderDataSource' || key === 'sliderDataQuery' || key === 'sliderDataVisibility';
        })) {
            score += 280;
        }
    }

    if (firstPanel) {
        score -= (firstPanel.order || 0) / 1000;
    }

    return score;
}

function nbhEntityHasPanelsForActiveTab(entityKey) {
    return nbhPanelsForTab(entityKey).length > 0;
}

function nbhPreferredEntityForActiveTab() {
    var entityKeys = nbhSelectableEntityKeys();
    var bestKey = nbhState.selectedEntity;
    var bestScore = -100001;
    var index;
    var score;

    for (index = 0; index < entityKeys.length; index++) {
        if (!nbhEntityHasPanelsForActiveTab(entityKeys[index])) {
            continue;
        }

        score = nbhEntityPriorityForActiveTab(entityKeys[index]);
        if (score > bestScore) {
            bestScore = score;
            bestKey = entityKeys[index];
        }
    }

    return bestKey;
}

function nbhSelectionHintForEntity(entityKey) {
    if (nbhState.activeTab === 'content' && (entityKey === 'slide' || entityKey === 'slides')) {
        return 'Здесь редактируется rail слайдов: ручной fallback, порядок карточек и их наполнение для slider contract.';
    }

    if (nbhState.activeTab === 'content' && entityKey === 'items') {
        return 'Здесь начинается работа с повторяющимся списком элементов: добавление, импорт, сортировка и редактирование карточек.';
    }

    if (nbhState.activeTab === 'content') {
        return 'Здесь редактируется одиночное наполнение выбранной сущности: текст, ссылка, медиа или другая отдельная часть блока.';
    }

    if (nbhState.activeTab === 'data' && (entityKey === 'slide' || entityKey === 'slides')) {
        return 'Здесь настраиваются list source, field mapping и visibility для slider rail без ручного fallback notice.';
    }

    if (nbhState.activeTab === 'data' && entityKey === 'items') {
        return 'Здесь настраиваются привязки коллекции и поведение карточек при работе с внешним источником данных.';
    }

    return 'Инспектор показал ближайшую сущность, для которой в этой вкладке действительно доступны настройки.';
}

function nbhEmptyStateMessage() {
    var entityLabel = nbhHumanEntity(nbhState.selectedEntity || 'section');
    var tabLabel = nbhTabLabel(nbhState.activeTab);

    if (nbhState.activeTab === 'design') {
        return 'Для сущности «' + entityLabel + '» во вкладке «' + tabLabel + '» пока нет доступных визуальных настроек. Попробуйте выбрать секцию или другую сущность с дизайном.';
    }

    if (nbhState.activeTab === 'layout') {
        return 'Для сущности «' + entityLabel + '» во вкладке «' + tabLabel + '» пока нет настроек компоновки. Обычно макет настраивается на уровне секции или группы элементов.';
    }

    if (nbhState.activeTab === 'data') {
        return 'Для сущности «' + entityLabel + '» во вкладке «' + tabLabel + '» пока нет источников данных или привязок. Выберите коллекцию, секцию или контентную сущность.';
    }

    return 'Для сущности «' + entityLabel + '» во вкладке «' + tabLabel + '» пока нет доступных панелей. Выберите другую сущность на превью или в списке справа.';
}

function nbhSetAutoSelectionNotice(fromEntity, toEntity, mode) {
    nbhState.autoSelectionNotice = {
        tab: nbhState.activeTab,
        mode: mode,
        fromEntity: fromEntity || '',
        toEntity: toEntity || ''
    };
}

function nbhRenderAutoSelectionNotice() {
    var notice = nbhState.autoSelectionNotice;
    var toLabel;
    var fromLabel;
    var title;
    var description;

    if (!notice || !notice.toEntity) {
        return '';
    }

    toLabel = nbhHumanEntity(notice.toEntity);
    fromLabel = notice.fromEntity ? nbhHumanEntity(notice.fromEntity) : '';
    title = notice.mode === 'primary'
        ? 'Открыт основной сценарий вкладки «' + nbhTabLabel(notice.tab) + '»'
        : 'Сущность переключена автоматически';

    if (notice.mode === 'primary') {
        description = 'Инспектор сразу показал «' + toLabel + '», потому что для этой вкладки это самый полезный стартовый сценарий редактирования.';
    } else {
        description = 'Для сущности «' + fromLabel + '» во вкладке «' + nbhTabLabel(notice.tab) + '» нет активных настроек, поэтому инспектор переключился на «' + toLabel + '».';
    }

    return '<div class="nbh-status-block nbh-status-block--auto">'
        + '<div class="nbh-status-block__head">'
        + '<span class="nbh-status-block__badge">Автовыбор</span>'
        + '<div class="nbh-status-block__title">' + nbhEscapeHtml(title) + '</div>'
        + '</div>'
        + '<p>' + nbhEscapeHtml(description) + '</p>'
        + '<div class="nbh-status-block__hint">' + nbhEscapeHtml(nbhSelectionHintForEntity(notice.toEntity)) + '</div>'
        + '</div>';
}

function nbhShouldPreferPrimaryEntityOnTabEnter() {
    return nbhState.activeTab === 'content';
}

function nbhEnsureSelectionForActiveTab(options) {
    var settings = options || {};
    var currentEntity = nbhState.selectedEntity;
    var currentHasPanels = nbhEntityHasPanelsForActiveTab(currentEntity);
    var nextEntity = nbhPreferredEntityForActiveTab();
    var shouldPreferPrimary = !!settings.preferPrimary && nbhShouldPreferPrimaryEntityOnTabEnter();

    if (currentHasPanels && (!shouldPreferPrimary || !nextEntity || nextEntity === currentEntity)) {
        if (!settings.preserveNotice) {
            nbhState.autoSelectionNotice = null;
        }
        return false;
    }

    if (!nextEntity || nextEntity === currentEntity) {
        if (!settings.preserveNotice) {
            nbhState.autoSelectionNotice = null;
        }
        return false;
    }

    nbhState.selectedEntity = nextEntity;
    nbhSetAutoSelectionNotice(currentEntity, nextEntity, currentHasPanels ? 'primary' : 'fallback');
    return true;
}

function nbhEntityChipList() {
    return nbhSelectableEntityKeys().filter(function(key) {
        return nbhEntityHasPanelsForActiveTab(key);
    }).map(function(key) {
        return '<button type="button" class="nbh-entity-chip' + (nbhState.selectedEntity === key ? ' is-active' : '') + '" data-entity="' + key + '">' + nbhHumanEntity(key) + '</button>';
    }).join('');
}