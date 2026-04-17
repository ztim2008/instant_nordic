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

function nbhPanelMatchesSelection(panel) {
    var selected = nbhState.selectedEntity;
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

function nbhPanelsForTab() {
    var panels = (nbhState.inspector && nbhState.inspector.availablePanels ? nbhState.inspector.availablePanels : []).filter(function(panel) {
        return nbhPanelVisible(panel) && nbhPanelMatchesSelection(panel);
    });
    var selected = nbhState.selectedEntity;

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

function nbhEntityChipList() {
    var resolved = nbhState.server.resolved.entities || {};
    return Object.keys(resolved).map(function(key) {
        return '<button type="button" class="nbh-entity-chip' + (nbhState.selectedEntity === key ? ' is-active' : '') + '" data-entity="' + key + '">' + nbhHumanEntity(key) + '</button>';
    }).join('');
}