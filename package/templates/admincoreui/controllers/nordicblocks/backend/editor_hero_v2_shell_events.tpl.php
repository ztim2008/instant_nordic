document.getElementById('nbhSaveBtn').addEventListener('click', function() {
    nbhSave(false);
});

document.getElementById('nbh-title-input').addEventListener('input', function() {
    nbhState.blockTitle = this.value;
    if (nbhState.draft && nbhState.draft.meta) {
        nbhState.draft.meta.label = this.value;
    }
    nbhMarkDirty();
    nbhScheduleSave();
});

document.getElementById('nbhTabs').addEventListener('click', function(event) {
    var target = event.target.closest('[data-tab]');
    var selectionChanged;

    if (!target) return;
    nbhState.activeTab = target.dataset.tab;
    selectionChanged = nbhEnsureSelectionForActiveTab({ preferPrimary: true });
    nbhRenderTabs();
    document.getElementById('nbhEntityList').innerHTML = nbhEntityChipList();
    nbhSelectEntity(nbhState.selectedEntity, true, { preserveNotice: selectionChanged });
});

document.getElementById('nbhEntityList').addEventListener('click', function(event) {
    var target = event.target.closest('[data-entity]');
    if (!target) return;
    nbhSelectEntity(target.dataset.entity, false);
});

document.getElementById('nbh-panel-body').addEventListener('input', function(event) {
    var target = event.target;
    var path;
    var value;

    if (target.closest('[data-breakpoint]')) return;
    if (target.dataset.colorPath) {
        path = target.dataset.colorPath;
        value = nbhSyncColorControls(path, target.value);
        nbhSet(nbhState.draft, path, value);
        if (nbhShouldRerenderPanels(path)) {
            nbhRenderPanels();
        }
        nbhMarkDirty();
        nbhScheduleSave();
        return;
    }
    if (target.dataset.colorText) {
        path = target.dataset.path;
        value = nbhPreviewColorControl(path, target.value);
        if (value) {
            nbhSet(nbhState.draft, path, value);
            if (nbhShouldRerenderPanels(path)) {
                nbhRenderPanels();
            }
            nbhMarkDirty();
            nbhScheduleSave();
        }
        return;
    }
    if (target.dataset.itemField) {
        nbhUpdateRepeaterItem(parseInt(target.dataset.itemIndex || '0', 10), target.dataset.itemField, target.value);
        return;
    }
    path = target.dataset.path;
    if (!path) return;

    if (path === 'layout.preset') {
        return;
    }

    value = target.value;
    if (target.dataset.type === 'number') {
        value = parseInt(value || '0', 10);
        if (isNaN(value)) value = 0;
    }

    nbhSet(nbhState.draft, path, value);
    if (nbhShouldRerenderPanels(path)) {
        nbhRenderPanels();
    }
    nbhMarkDirty();
    nbhScheduleSave();
});

document.getElementById('nbh-panel-body').addEventListener('change', function(event) {
    var breakpointTarget = event.target.closest('[data-breakpoint]');
    var target = event.target;
    var path;
    var value;

    if (breakpointTarget) {
        nbhState.activeBreakpoint = breakpointTarget.dataset.breakpoint;
        nbhRenderPanels();
        return;
    }

    if (target.dataset.colorPath) {
        path = target.dataset.colorPath;
        value = nbhSyncColorControls(path, target.value);
        nbhCommitPathValue(path, value, false);
        return;
    }

    if (target.dataset.colorText) {
        path = target.dataset.path;
        value = nbhSyncColorControls(path, target.value);
        nbhCommitPathValue(path, value, false);
        return;
    }

    if (target.dataset.itemField) {
        nbhUpdateRepeaterItem(parseInt(target.dataset.itemIndex || '0', 10), target.dataset.itemField, target.value);
        return;
    }
    path = target.dataset.path;
    if (!path) return;

    if (path === 'layout.preset') {
        if (nbhApplyHeroPreset(target.value)) {
            nbhRenderPanels();
            nbhMarkDirty();
            nbhScheduleSave();
            return;
        }
    }

    value = target.value;
    if (target.dataset.type === 'number') {
        value = parseInt(value || '0', 10);
        if (isNaN(value)) value = 0;
    }

    nbhCommitPathValue(path, value, false);
});

document.getElementById('nbh-panel-body').addEventListener('click', function(event) {
    var pickerAction = event.target.closest('[data-picker-action]');
    var catalogTableAction = event.target.closest('[data-catalog-table-action]');

    if (catalogTableAction) {
        if (catalogTableAction.dataset.catalogTableAction === 'open') {
            nbhOpenCatalogTableModal('current');
        }
        return;
    }

    if (pickerAction) {
        event.preventDefault();
        var pickerPath = pickerAction.dataset.path || '';
        if (pickerAction.dataset.pickerAction === 'clear') {
            nbhCommitPathValue(pickerPath, '', true);
            return;
        }

        if (pickerAction.dataset.pickerKind === 'image') {
            nbhOpenImagePicker(pickerPath);
            return;
        }

        if (pickerAction.dataset.pickerKind === 'icon') {
            nbhOpenIconPicker(pickerPath);
            return;
        }
    }

    var repeaterAction = event.target.closest('[data-repeater-action]');
    if (repeaterAction) {
        if (repeaterAction.dataset.repeaterAction === 'add') {
            nbhAddRepeaterItem();
        }
        if (repeaterAction.dataset.repeaterAction === 'duplicate') {
            nbhDuplicateRepeaterItem(parseInt(repeaterAction.dataset.itemIndex || '0', 10));
        }
        if (repeaterAction.dataset.repeaterAction === 'move-up') {
            nbhMoveRepeaterItem(parseInt(repeaterAction.dataset.itemIndex || '0', 10), -1);
        }
        if (repeaterAction.dataset.repeaterAction === 'move-down') {
            nbhMoveRepeaterItem(parseInt(repeaterAction.dataset.itemIndex || '0', 10), 1);
        }
        if (repeaterAction.dataset.repeaterAction === 'remove') {
            nbhRemoveRepeaterItem(parseInt(repeaterAction.dataset.itemIndex || '0', 10));
        }
        return;
    }

    var accordionToggle = event.target.closest('[data-accordion-key]');
    if (accordionToggle) {
        nbhState.openAccordionByTab[nbhState.activeTab] = accordionToggle.dataset.accordionKey || '';
        nbhRenderPanels();
        return;
    }

    var target = event.target.closest('[data-breakpoint]');
    if (!target) return;
    nbhState.activeBreakpoint = target.dataset.breakpoint;
    nbhRenderPanels();
});

document.getElementById('nbhVpDesktop').addEventListener('click', function() { nbhSetViewport('desktop'); });
document.getElementById('nbhVpMobile').addEventListener('click', function() { nbhSetViewport('mobile'); });

document.getElementById('nbh-canvas-frame').addEventListener('load', function() {
    setTimeout(nbhSyncCanvasHeightFromFrame, 20);
    nbhState.canvas.ready = false;
    nbhState.canvas.availableEntities = [];
    nbhState.canvas.selectedEntity = '';
    setTimeout(function() {
        nbhRequestCanvasState('iframe-load');
    }, 60);
});

window.addEventListener('message', function(event) {
    var data = event.data || {};
    if (data.source !== 'nordicblocks-canvas') return;
    if (data.type === 'canvas:metrics') {
        nbhApplyCanvasHeight(data.height);
        return;
    }
    if (data.type === 'canvas:ready') {
        nbhApplyCanvasState(data);
        document.getElementById('nbhEntityList').innerHTML = nbhEntityChipList();
        nbhSyncCanvasSelection('canvas-ready');
        return;
    }
    if (data.type === 'canvas:selection') {
        nbhApplyCanvasState(data);
        document.getElementById('nbhEntityList').innerHTML = nbhEntityChipList();
        if (data.entity) {
            nbhSelectEntity(data.entity, true, { preserveNotice: true, reason: data.reason || 'canvas-selection' });
        } else if (nbhState.selectedEntity) {
            nbhSelectEntity('', true, { preserveNotice: true, reason: data.reason || 'canvas-selection-empty' });
        }
        return;
    }
    if (data.type === 'entity:selected' && data.entity) {
        nbhApplyCanvasState(data);
        document.getElementById('nbhEntityList').innerHTML = nbhEntityChipList();
        nbhSelectEntity(data.entity, true, { preserveNotice: true, reason: 'canvas-legacy-selection' });
    }
});

document.addEventListener('keydown', function(event) {
    if ((event.ctrlKey || event.metaKey) && event.key === 's') {
        event.preventDefault();
        nbhSave(false);
    }
});

window.addEventListener('beforeunload', function(event) {
    if (!nbhState.dirty) return;
    event.preventDefault();
    event.returnValue = '';
});

nbhLoadState().catch(function(error) {
    document.getElementById('nbh-panel-body').innerHTML = '<div class="nbh-empty">Не удалось загрузить block editor state: ' + (error && error.message ? error.message : 'unknown') + '</div>';
});