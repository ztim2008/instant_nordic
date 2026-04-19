function nbhDefaultControlComponent(controlKey) {
    var componentMap = {
        textContent: 'text-content-panel',
        buttonContent: 'button-content-panel',
        mediaContent: 'media-content-panel',
        sectionBackground: 'section-background-panel',
        sectionContainer: 'section-container-panel',
        typographyText: 'typography-text-panel',
        buttonStyle: 'button-style-panel',
        surfaceStyle: 'surface-style-panel',
        spacingLayout: 'spacing-layout-panel',
        alignmentLayout: 'alignment-layout-panel',
        dataSource: 'data-source-panel',
        dataCollection: 'data-collection-panel',
        repeaterItems: 'repeater-items-panel'
    };

    return componentMap[controlKey] || '';
}

function nbhResolveControlDefinition(panel) {
    var controlKey = nbhPanelControlKey(panel);
    var controls = nbhState.inspector && nbhState.inspector.controls ? nbhState.inspector.controls : {};
    var controlPresets = nbhState.inspector && nbhState.inspector.controlPresets ? nbhState.inspector.controlPresets : {};
    var definition = controls[controlKey] || controlPresets[controlKey] || {};

    return {
        key: controlKey,
        label: panel && panel.controlLabel ? panel.controlLabel : (definition.label || controlKey),
        component: panel && panel.controlComponent ? panel.controlComponent : (definition.component || nbhDefaultControlComponent(controlKey))
    };
}

<?php include __DIR__ . '/editor_hero_v2_control_renderers_content.tpl.php'; ?>
<?php include __DIR__ . '/editor_hero_v2_control_renderers_design.tpl.php'; ?>
<?php include __DIR__ . '/editor_hero_v2_control_renderers_css.tpl.php'; ?>
<?php include __DIR__ . '/editor_hero_v2_control_renderers_layout.tpl.php'; ?>
<?php include __DIR__ . '/editor_hero_v2_control_renderers_data.tpl.php'; ?>
<?php include __DIR__ . '/editor_hero_v2_control_renderers_repeater.tpl.php'; ?>

var nbhControlComponentRenderers = Object.assign({}, nbhBuildContentControlRenderers(), nbhBuildDesignControlRenderers(), nbhBuildCssControlRenderers(), nbhBuildLayoutControlRenderers(), nbhBuildDataControlRenderers(), nbhBuildRepeaterControlRenderers());

function nbhRenderPanel(panel) {
    var bp = nbhState.activeBreakpoint;
    var control = nbhResolveControlDefinition(panel);
    var renderer = nbhControlComponentRenderers[control.component] || nbhControlComponentRenderers.__default;
    var body = renderer(panel, bp, control);

    return '<section class="nbh-section" data-panel="' + panel.key + '">' +
        '<div class="nbh-section-head"><strong>' + panel.label + '</strong><span>' + nbhHumanSection(panel.section) + '</span></div>' +
        '<div class="nbh-section-body">' + body + '</div>' +
    '</section>';
}

function nbhRenderPanels() {
    var panels = nbhPanelsForTab();
    var body = document.getElementById('nbh-panel-body');
    var groups;
    var activeKey;
    var noticeHtml = nbhRenderAutoSelectionNotice();

    if (!panels.length) {
        body.innerHTML = noticeHtml + '<div class="nbh-empty">' + nbhEscapeHtml(nbhEmptyStateMessage()) + '</div>';
        return;
    }

    groups = nbhPanelSectionGroups(panels);
    activeKey = nbhActiveAccordionKey(groups);
    body.innerHTML = noticeHtml + '<div class="nbh-accordion">' + groups.map(function(group) {
        return nbhRenderAccordionGroup(group, activeKey);
    }).join('') + '</div>';
}

function nbhRender() {
    if (!nbhState.loaded) return;
    var selectionChanged = nbhEnsureSelectionForActiveTab({ preferPrimary: true });
    document.getElementById('nbhEntityList').innerHTML = nbhEntityChipList();
    nbhRenderTabs();
    nbhSelectEntity(nbhState.selectedEntity, false, { preserveNotice: selectionChanged });
}