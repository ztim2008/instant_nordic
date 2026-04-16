<?php
$block_title_esc = htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8');
$block_type      = htmlspecialchars($block['type'], ENT_QUOTES, 'UTF-8');
?>
<style>
#nbh-shell {
    position: fixed;
    inset: 55px 0 0 0;
    display: flex;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
    background: #edf2f7;
    z-index: 100;
}
#nbh-topbar {
    height: 52px;
    background: #172033;
    border-bottom: 1px solid #0f172a;
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: 0 .8rem;
}
.nbh-back {
    color: #a5b4c7;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .78rem;
    padding: .3rem .5rem;
    border-radius: 6px;
}
.nbh-back:hover { background: rgba(255,255,255,.08); color: #e5edf7; }
.nbh-sep { width: 1px; height: 24px; background: #334155; }
.nbh-title-wrap { display: flex; align-items: center; gap: .45rem; }
#nbh-title-input {
    width: 240px;
    background: transparent;
    color: #f8fafc;
    border: none;
    border-bottom: 1px solid transparent;
    outline: none;
    font-size: .86rem;
    font-weight: 700;
    padding: .15rem .2rem;
}
#nbh-title-input:focus { border-bottom-color: #60a5fa; }
.nbh-type {
    font-size: .68rem;
    color: #94a3b8;
    background: #0f172a;
    border-radius: 999px;
    padding: .18rem .55rem;
    font-family: monospace;
}
.nbh-spacer { flex: 1; }
.nbh-vp {
    display: inline-flex;
    gap: 2px;
    padding: 2px;
    background: #0f172a;
    border-radius: 8px;
}
.nbh-vp button,
.nbh-btn {
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: .77rem;
    font-weight: 600;
}
.nbh-vp button {
    padding: .34rem .72rem;
    color: #94a3b8;
    background: transparent;
}
.nbh-vp button.is-active { background: #1e293b; color: #f8fafc; }
.nbh-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .42rem .9rem;
}
.nbh-btn--ghost { background: rgba(255,255,255,.08); color: #d7e0eb; }
.nbh-btn--ghost:hover { background: rgba(255,255,255,.14); }
.nbh-btn--save { background: #16a34a; color: #fff; }
.nbh-btn--save:hover { background: #15803d; }
.nbh-btn--save.is-dirty { box-shadow: 0 0 0 2px rgba(251,191,36,.55); }
.nbh-btn--save.is-saving { background: #475569; pointer-events: none; }

#nbh-body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 0;
    min-height: 0;
    flex: 1;
    overflow: hidden;
}
#nbh-canvas-wrap {
    padding: 16px;
    overflow: hidden;
    display: flex;
    align-items: stretch;
    justify-content: center;
    min-height: 0;
    background: radial-gradient(circle at top left, rgba(96,165,250,.10), transparent 30%), #e2e8f0;
}
#nbh-canvas-frame {
    width: 100%;
    max-width: 1280px;
    border: none;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 18px 40px rgba(15,23,42,.16);
}
#nbh-canvas-frame.is-mobile { max-width: 390px; }

#nbh-panel {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border-left: 1px solid #dbe4ef;
}
.nbh-panel-head {
    padding: .85rem 1rem .7rem;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    flex-direction: column;
    gap: .7rem;
}
.nbh-panel-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.nbh-panel-title strong {
    font-size: .9rem;
    color: #0f172a;
}
.nbh-selection {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .28rem .58rem;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: .7rem;
    font-weight: 700;
}
.nbh-entity-list {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
}
.nbh-entity-chip {
    border: 1px solid #dbe4ef;
    background: #fff;
    color: #475569;
    border-radius: 999px;
    padding: .32rem .58rem;
    font-size: .72rem;
    font-weight: 600;
    cursor: pointer;
}
.nbh-entity-chip:hover { border-color: #93c5fd; background: #f8fbff; color: #1d4ed8; }
.nbh-entity-chip.is-active { background: #dbeafe; border-color: #60a5fa; color: #1d4ed8; }

.nbh-tabs {
    display: flex;
    gap: .35rem;
    border-bottom: 1px solid #edf2f7;
    padding: .75rem .85rem .65rem;
}
.nbh-tab {
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    font-size: .76rem;
    font-weight: 700;
    padding: .45rem .7rem;
    border-radius: 8px;
}
.nbh-tab.is-active { background: #eff6ff; color: #1d4ed8; }

#nbh-panel-body {
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: .85rem;
    display: flex;
    flex-direction: column;
    gap: .85rem;
}
.nbh-empty {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    padding: 1rem;
    color: #64748b;
    font-size: .8rem;
    line-height: 1.5;
}
.nbh-section {
    border: 1px solid #e5edf5;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
}
.nbh-section-head {
    padding: .75rem .9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    border-bottom: 1px solid #edf2f7;
    background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
}
.nbh-section-head strong {
    font-size: .81rem;
    color: #0f172a;
}
.nbh-section-head span {
    font-size: .68rem;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.nbh-section-body {
    padding: .85rem .9rem;
    display: flex;
    flex-direction: column;
    gap: .8rem;
}
.nbh-field {
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.nbh-field label {
    font-size: .73rem;
    font-weight: 700;
    color: #475569;
}
.nbh-field input,
.nbh-field textarea,
.nbh-field select {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d1d9e6;
    border-radius: 8px;
    background: #fff;
    color: #0f172a;
    padding: .5rem .65rem;
    font-size: .82rem;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.nbh-field textarea { min-height: 78px; resize: vertical; }
.nbh-field input:focus,
.nbh-field textarea:focus,
.nbh-field select:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(59,130,246,.14);
}
.nbh-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem;
}
.nbh-note {
    padding: .8rem .9rem;
    border-radius: 10px;
    background: #f8fafc;
    color: #64748b;
    font-size: .76rem;
    line-height: 1.5;
}
.nbh-breakpoints {
    display: inline-flex;
    gap: 2px;
    padding: 2px;
    border-radius: 8px;
    background: #eef2f7;
}
.nbh-breakpoints button {
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    font-size: .7rem;
    font-weight: 700;
    padding: .32rem .52rem;
    border-radius: 6px;
}
.nbh-breakpoints button.is-active { background: #fff; color: #1d4ed8; box-shadow: 0 1px 2px rgba(15,23,42,.06); }
@media (max-width: 1180px) {
    #nbh-body { grid-template-columns: minmax(0, 1fr) 330px; }
}
</style>

<div id="nbh-shell">
    <div id="nbh-topbar">
        <a href="<?= htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8') ?>" class="nbh-back"><i class="fa fa-chevron-left"></i> Блоки</a>
        <div class="nbh-sep"></div>
        <div class="nbh-title-wrap">
            <input type="text" id="nbh-title-input" value="<?= $block_title_esc ?>" placeholder="Название блока">
            <span class="nbh-type"><?= $block_type ?></span>
        </div>
        <div class="nbh-spacer"></div>
        <div class="nbh-vp">
            <button type="button" class="is-active" id="nbhVpDesktop">Desktop</button>
            <button type="button" id="nbhVpMobile">Mobile</button>
        </div>
        <div class="nbh-sep"></div>
        <a class="nbh-btn nbh-btn--ghost" href="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-thumb-tack"></i> Разместить</a>
        <button class="nbh-btn nbh-btn--save" id="nbhSaveBtn"><i class="fa fa-save"></i> Сохранить</button>
    </div>

    <div id="nbh-body">
        <div id="nbh-canvas-wrap">
            <iframe id="nbh-canvas-frame" src="<?= htmlspecialchars($canvas_url, ENT_QUOTES, 'UTF-8') ?>" title="Block preview" sandbox="allow-same-origin allow-scripts"></iframe>
        </div>

        <div id="nbh-panel">
            <div class="nbh-panel-head">
                <div class="nbh-panel-title">
                    <strong>Inspector Shell v2</strong>
                    <span class="nbh-selection" id="nbhSelectionLabel">Entity: title</span>
                </div>
                <div class="nbh-entity-list" id="nbhEntityList"></div>
            </div>

            <div class="nbh-tabs" id="nbhTabs"></div>
            <div id="nbh-panel-body"></div>
        </div>
    </div>
</div>

<script>
var nbhSaveUrl = <?= json_encode($save_url, JSON_UNESCAPED_UNICODE) ?>;
var nbhEditorStateUrl = <?= json_encode($editor_state_url, JSON_UNESCAPED_UNICODE) ?>;
var nbhCanvasUrl = <?= json_encode($canvas_url, JSON_UNESCAPED_UNICODE) ?>;
var nbhCsrfToken = <?= json_encode(cmsForm::getCSRFToken(), JSON_UNESCAPED_UNICODE) ?>;

var nbhState = {
    loaded: false,
    server: null,
    inspector: null,
    draft: null,
    blockTitle: document.getElementById('nbh-title-input').value || '',
    selectedEntity: 'title',
    activeTab: 'content',
    activeBreakpoint: 'desktop',
    dirty: false,
    saving: false,
    queuedSave: false,
    queuedSilent: true,
    debounceTimer: null
};

function nbhClone(value) {
    return JSON.parse(JSON.stringify(value));
}

function nbhGet(obj, path, fallback) {
    var parts = String(path || '').split('.');
    var current = obj;
    for (var i = 0; i < parts.length; i++) {
        if (!parts[i]) continue;
        if (!current || typeof current !== 'object' || !(parts[i] in current)) {
            return fallback;
        }
        current = current[parts[i]];
    }
    return current === undefined ? fallback : current;
}

function nbhSet(obj, path, value) {
    var parts = String(path || '').split('.');
    var current = obj;
    for (var i = 0; i < parts.length - 1; i++) {
        var key = parts[i];
        if (!key) continue;
        if (!current[key] || typeof current[key] !== 'object') {
            current[key] = {};
        }
        current = current[key];
    }
    current[parts[parts.length - 1]] = value;
}

function nbhHumanEntity(entityKey) {
    var registry = nbhState.server && nbhState.server.registry ? nbhState.server.registry.entities : null;
    return registry && registry[entityKey] ? registry[entityKey].label : entityKey;
}

function nbhBlockType() {
    return nbhGet(nbhState.server, 'block.type', '');
}

function nbhRepeaterItems() {
    var items = nbhGet(nbhState.draft, 'content.items', []);
    return Array.isArray(items) ? items : [];
}

function nbhDataOptions() {
    var options = nbhState.server && nbhState.server.dataOptions ? nbhState.server.dataOptions : null;
    if (!options || typeof options !== 'object') {
        return { contentTypes: [], fieldsByType: {}, listModes: [], sortOptions: [] };
    }

    options.contentTypes = Array.isArray(options.contentTypes) ? options.contentTypes : [];
    options.fieldsByType = options.fieldsByType && typeof options.fieldsByType === 'object' ? options.fieldsByType : {};
    options.listModes = Array.isArray(options.listModes) ? options.listModes : [];
    options.sortOptions = Array.isArray(options.sortOptions) ? options.sortOptions : [];
    return options;
}

function nbhFaqListSource() {
    var source = nbhGet(nbhState.draft, 'data.listSource', null);
    if (!source || typeof source !== 'object' || Array.isArray(source)) {
        source = {};
    }

    if (!source.type) source.type = 'manual';
    if (!source.ctype) source.ctype = '';
    if (typeof source.limit !== 'number') source.limit = 3;
    if (!source.sort) source.sort = 'date_pub_desc';
    if (!source.map || typeof source.map !== 'object' || Array.isArray(source.map)) {
        source.map = {};
    }
    if (typeof source.map.question !== 'string') source.map.question = 'title';
    if (typeof source.map.answer !== 'string') source.map.answer = '';
    if (!source.emptyBehavior) source.emptyBehavior = 'fallback';

    nbhSet(nbhState.draft, 'data.listSource', source);
    return source;
}

function nbhEscapeAttr(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
}

function nbhEscapeHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function nbhAddRepeaterItem() {
    var items = nbhRepeaterItems().slice();
    items.push({ question: 'Новый вопрос', answer: 'Короткий ответ на вопрос.' });
    nbhSet(nbhState.draft, 'content.items', items);
    nbhMarkDirty();
    nbhRenderPanels();
    nbhScheduleSave();
}

function nbhRemoveRepeaterItem(index) {
    var items = nbhRepeaterItems().slice();
    if (index < 0 || index >= items.length) {
        return;
    }
    items.splice(index, 1);
    nbhSet(nbhState.draft, 'content.items', items);
    nbhMarkDirty();
    nbhRenderPanels();
    nbhScheduleSave();
}

function nbhUpdateRepeaterItem(index, field, value) {
    var items = nbhRepeaterItems().slice();
    if (!items[index] || typeof items[index] !== 'object') {
        items[index] = { question: '', answer: '' };
    }
    items[index][field] = value;
    nbhSet(nbhState.draft, 'content.items', items);
    nbhMarkDirty();
    nbhScheduleSave();
}

function nbhMarkDirty() {
    nbhState.dirty = true;
    document.getElementById('nbhSaveBtn').classList.add('is-dirty');
}

function nbhScheduleSave() {
    clearTimeout(nbhState.debounceTimer);
    nbhState.debounceTimer = setTimeout(function() {
        nbhSave(true);
    }, 350);
}

function nbhReloadCanvas() {
    var frame = document.getElementById('nbh-canvas-frame');
    frame.src = nbhCanvasUrl + (nbhCanvasUrl.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
}

function nbhSelectEntity(entityKey, fromCanvas) {
    if (!entityKey) return;
    nbhState.selectedEntity = entityKey;
    var label = document.getElementById('nbhSelectionLabel');
    if (label) {
        label.textContent = 'Entity: ' + nbhHumanEntity(entityKey);
    }
    document.querySelectorAll('.nbh-entity-chip').forEach(function(chip) {
        chip.classList.toggle('is-active', chip.dataset.entity === entityKey);
    });
    nbhRenderPanels();
    if (!fromCanvas) {
        var frame = document.getElementById('nbh-canvas-frame');
        if (frame && frame.contentWindow) {
            frame.contentWindow.postMessage({ source: 'nordicblocks-editor', type: 'entity:select', entity: entityKey }, '*');
        }
    }
}

function nbhSetViewport(mode) {
    var frame = document.getElementById('nbh-canvas-frame');
    document.getElementById('nbhVpDesktop').classList.toggle('is-active', mode === 'desktop');
    document.getElementById('nbhVpMobile').classList.toggle('is-active', mode === 'mobile');
    frame.classList.toggle('is-mobile', mode === 'mobile');
}

function nbhBuildInspectorState(payload) {
    return {
        entityGroups: nbhGet(payload, 'inspector.entityGroups', {}),
        controlPresets: nbhGet(payload, 'inspector.controlPresets', {}),
        availablePanels: nbhGet(payload, 'inspector.availablePanels', nbhGet(payload, 'registry.panels', [])),
        tabs: nbhGet(payload, 'inspector.tabs', nbhGet(payload, 'registry.tabs', [])),
        selectionModel: nbhGet(payload, 'inspector.selectionModel', {})
    };
}

function nbhLoadState() {
    return fetch(nbhEditorStateUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(response) { return response.json(); })
        .then(function(payload) {
            if (!payload.ok) {
                throw new Error(payload.error || 'state_error');
            }
            nbhState.server = payload;
            nbhState.draft = nbhClone(payload.contract);
            nbhState.inspector = nbhBuildInspectorState(payload);
            nbhState.activeTab = payload.ui && payload.ui.activeTab ? payload.ui.activeTab : 'content';
            nbhState.activeBreakpoint = payload.ui && payload.ui.activeBreakpoint ? payload.ui.activeBreakpoint : 'desktop';
            nbhState.selectedEntity = payload.ui && payload.ui.selectedEntity ? payload.ui.selectedEntity : 'title';
            nbhState.loaded = true;
            nbhRender();
        });
}

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

function nbhEntityChipList() {
    var resolved = nbhState.server.resolved.entities || {};
    return Object.keys(resolved).map(function(key) {
        return '<button type="button" class="nbh-entity-chip' + (nbhState.selectedEntity === key ? ' is-active' : '') + '" data-entity="' + key + '">' + nbhHumanEntity(key) + '</button>';
    }).join('');
}

function nbhSave(silent) {
    if (!nbhState.loaded) return;
    if (silent && !nbhState.dirty) return;

    if (nbhState.saving) {
        nbhState.queuedSave = true;
        nbhState.queuedSilent = nbhState.queuedSilent && silent;
        return;
    }

    nbhState.saving = true;
    var btn = document.getElementById('nbhSaveBtn');
    btn.classList.add('is-saving');
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Сохранение...';

    fetch(nbhSaveUrl + '?csrf_token=' + encodeURIComponent(nbhCsrfToken), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            title: nbhState.blockTitle,
            contract: nbhState.draft
        })
    })
        .then(function(response) { return response.json(); })
        .then(function(payload) {
            nbhState.saving = false;
            btn.classList.remove('is-saving');

            if (!payload.ok) {
                btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
                alert('Ошибка: ' + (payload.error || '?'));
                return;
            }

            if (payload.contract) {
                nbhState.draft = nbhClone(payload.contract);
            }

            nbhState.dirty = false;
            btn.classList.remove('is-dirty');
            btn.innerHTML = '<i class="fa fa-check"></i> Сохранено';
            nbhReloadCanvas();

            setTimeout(function() {
                btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
            }, 1600);

            if (nbhState.queuedSave) {
                var queuedSilent = nbhState.queuedSilent;
                nbhState.queuedSave = false;
                nbhState.queuedSilent = true;
                nbhSave(queuedSilent);
            }
        })
        .catch(function() {
            nbhState.saving = false;
            btn.classList.remove('is-saving');
            btn.innerHTML = '<i class="fa fa-save"></i> Сохранить';
        });
}

function nbhRenderTabs() {
    var tabs = (nbhState.inspector && nbhState.inspector.tabs ? nbhState.inspector.tabs : []).slice().sort(function(a, b) { return (a.order || 0) - (b.order || 0); });
    document.getElementById('nbhTabs').innerHTML = tabs.map(function(tab) {
        return '<button type="button" class="nbh-tab' + (tab.key === nbhState.activeTab ? ' is-active' : '') + '" data-tab="' + tab.key + '">' + tab.label + '</button>';
    }).join('');
}

function nbhField(label, controlHtml) {
    return '<div class="nbh-field"><label>' + label + '</label>' + controlHtml + '</div>';
}

function nbhInput(path, options) {
    options = options || {};
    var value = nbhGet(nbhState.draft, path, options.fallback || '');
    var attrs = 'data-path="' + path + '"';
    if (options.type) attrs += ' data-type="' + options.type + '"';
    return '<input ' + attrs + ' type="' + (options.inputType || 'text') + '" value="' + String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '">';
}

function nbhTextarea(path, fallback) {
    var value = nbhGet(nbhState.draft, path, fallback || '');
    return '<textarea data-path="' + path + '">' + String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</textarea>';
}

function nbhSelect(path, options, fallback) {
    var value = nbhGet(nbhState.draft, path, fallback || '');
    if (typeof value === 'boolean') {
        value = value ? '1' : '0';
    }
    value = String(value);
    return '<select data-path="' + path + '">' + options.map(function(option) {
        return '<option value="' + option.value + '"' + (value === option.value ? ' selected' : '') + '>' + option.label + '</option>';
    }).join('') + '</select>';
}

function nbhShouldRerenderPanels(path) {
    return path.indexOf('data.listSource.') === 0 || path.indexOf('design.section.background.') === 0;
}

function nbhYesNoOptions() {
    return [
        { value: '1', label: 'Показывать' },
        { value: '0', label: 'Скрыть' }
    ];
}

function nbhBreakpointToggle() {
    return '<div class="nbh-breakpoints"><button type="button" data-breakpoint="desktop" class="' + (nbhState.activeBreakpoint === 'desktop' ? 'is-active' : '') + '">Desktop</button><button type="button" data-breakpoint="mobile" class="' + (nbhState.activeBreakpoint === 'mobile' ? 'is-active' : '') + '">Mobile</button></div>';
}

function nbhRepeaterEditor() {
    var items = nbhRepeaterItems();
    var listSource = nbhFaqListSource();
    var cards = items.map(function(item, index) {
        var question = item && item.question ? item.question : '';
        var answer = item && item.answer ? item.answer : '';
        return '<div class="nbh-note" style="background:#fff;border:1px solid #dbe4ef;">'
            + '<div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.75rem;">'
            + '<strong>Вопрос ' + (index + 1) + '</strong>'
            + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="remove" data-item-index="' + index + '" style="padding:.32rem .7rem;font-size:.72rem;">Удалить</button>'
            + '</div>'
            + nbhField('Вопрос', '<input type="text" data-item-field="question" data-item-index="' + index + '" value="' + nbhEscapeAttr(question) + '">')
            + nbhField('Ответ', '<textarea data-item-field="answer" data-item-index="' + index + '">' + nbhEscapeHtml(answer) + '</textarea>')
            + '</div>';
    }).join('');

    if (!cards) {
        cards = '<div class="nbh-note">Список FAQ пока пуст. Добавьте первый вопрос.</div>';
    }

    if (listSource.type === 'content_list') {
        cards = '<div class="nbh-note">Ручные вопросы ниже остаются fallback-списком, если data adapter не вернёт записей.</div>' + cards;
    }

    return nbhField('Первый вопрос открыт', nbhSelect('runtime.disclosure.openFirst', [
        { value: '1', label: 'Да' },
        { value: '0', label: 'Нет' }
    ], '1'))
        + cards
        + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="add" style="align-self:flex-start;"><i class="fa fa-plus"></i> Добавить вопрос</button>';
}

var nbhPresetRenderers = {
    textContent: function(panel) {
        if (panel.entityScope === 'eyebrow') {
            return nbhField('Текст', nbhInput('content.eyebrow'));
        }
        if (panel.entityScope === 'title') {
            return nbhField('Отображение', nbhSelect('design.entities.title.visible', nbhYesNoOptions(), '1'))
                + nbhField('Текст', nbhTextarea('content.title', ''));
        }
        if (panel.entityScope === 'subtitle') {
            return nbhField('Отображение', nbhSelect('design.entities.subtitle.visible', nbhYesNoOptions(), '1'))
                + nbhField('Текст', nbhTextarea('content.subtitle', ''));
        }
        return '<div class="nbh-note">Нет text mapping для сущности ' + panel.entityScope + '.</div>';
    },
    buttonContent: function() {
        return '<div class="nbh-grid-2">'
            + nbhField('Primary label', nbhInput('content.primaryButton.label'))
            + nbhField('Primary URL', nbhInput('content.primaryButton.url'))
            + nbhField('Secondary label', nbhInput('content.secondaryButton.label'))
            + nbhField('Secondary URL', nbhInput('content.secondaryButton.url'))
            + '</div>';
    },
    mediaContent: function() {
        return nbhField('Путь к изображению', nbhInput('content.media.image'))
            + nbhField('Alt', nbhInput('content.media.alt'));
    },
    sectionBackground: function() {
        var backgroundMode = String(nbhGet(nbhState.draft, 'design.section.background.mode', 'theme') || 'theme');
        var options = nbhBlockType() === 'faq'
            ? [
                { value: 'light', label: 'Светлая' },
                { value: 'alt', label: 'Серый фон' },
                { value: 'dark', label: 'Темная' }
            ]
            : [
                { value: 'light', label: 'Светлая' },
                { value: 'dark', label: 'Темная' },
                { value: 'accent', label: 'Accent' }
            ];
        var body = nbhField('Тема блока', nbhSelect('design.section.theme', options, 'light'));
        body += nbhField('Режим фона', nbhSelect('design.section.background.mode', [
            { value: 'theme', label: 'Из темы блока' },
            { value: 'color', label: 'Сплошной цвет' },
            { value: 'gradient', label: 'Градиент' },
            { value: 'image', label: 'Фото + затемнение' }
        ], 'theme'));

        if (backgroundMode === 'color') {
            body += nbhField('Цвет фона', nbhInput('design.section.background.color', { inputType: 'color', fallback: '#f8fafc' }));
        }

        if (backgroundMode === 'gradient') {
            body += '<div class="nbh-grid-2">'
                + nbhField('Цвет 1', nbhInput('design.section.background.gradientFrom', { inputType: 'color', fallback: '#f8fafc' }))
                + nbhField('Цвет 2', nbhInput('design.section.background.gradientTo', { inputType: 'color', fallback: '#dbeafe' }))
                + nbhField('Угол', nbhInput('design.section.background.gradientAngle', { inputType: 'number', type: 'number', fallback: 135 }))
                + '</div>';
        }

        if (backgroundMode === 'image') {
            body += nbhField('Путь к фото', nbhInput('design.section.background.image'));
            body += '<div class="nbh-grid-2">'
                + nbhField('Цвет затемнения', nbhInput('design.section.background.overlayColor', { inputType: 'color', fallback: '#0f172a' }))
                + nbhField('Сила затемнения, %', nbhInput('design.section.background.overlayOpacity', { inputType: 'number', type: 'number', fallback: 45 }))
                + '</div>';
        }

        return body;
    },
    sectionContainer: function() {
        return nbhField('Ширина контента', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: nbhBlockType() === 'faq' ? 760 : 640 }));
    },
    typographyText: function(panel, bp) {
        var body = nbhBreakpointToggle();
        var blockType = nbhBlockType();
        if (panel.entityScope === 'title') {
            body += '<div class="nbh-grid-2">'
                + nbhField('Размер', nbhInput('design.entities.title.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? (blockType === 'faq' ? 48 : 64) : (blockType === 'faq' ? 32 : 40) }))
                + nbhField('Отступ снизу', nbhInput('design.entities.title.' + bp + '.marginBottom', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? (blockType === 'faq' ? 0 : 16) : (blockType === 'faq' ? 0 : 14) }))
                + (bp === 'desktop'
                    ? nbhField('Жирность', nbhSelect('design.entities.title.weight', [
                        { value: '400', label: '400' },
                        { value: '500', label: '500' },
                        { value: '600', label: '600' },
                        { value: '700', label: '700' },
                        { value: '800', label: '800' },
                        { value: '900', label: '900' }
                    ], blockType === 'faq' ? '800' : '900'))
                    : '')
                + '</div>';
            if (bp === 'desktop') {
                body += nbhField('HTML тег', nbhSelect('design.entities.title.tag', [
                    { value: 'div', label: 'DIV' },
                    { value: 'h1', label: 'H1' },
                    { value: 'h2', label: 'H2' },
                    { value: 'h3', label: 'H3' }
                ], blockType === 'faq' ? 'h2' : 'h1'));
            }
            return body;
        }
        if (panel.entityScope === 'subtitle') {
            return body + '<div class="nbh-grid-2">'
                + nbhField('Размер', nbhInput('design.entities.subtitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? (blockType === 'faq' ? 18 : 20) : (blockType === 'faq' ? 16 : 18) }))
                + nbhField('Отступ снизу', nbhInput('design.entities.subtitle.' + bp + '.marginBottom', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? (blockType === 'faq' ? 32 : 24) : (blockType === 'faq' ? 24 : 20) }))
                + '</div>';
        }
        if (panel.entityScope === 'items' && blockType === 'faq') {
            body += '<div class="nbh-grid-2">'
                + nbhField('Размер вопроса', nbhInput('design.entities.itemTitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? 18 : 17 }))
                + nbhField('Размер ответа', nbhInput('design.entities.itemText.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? 16 : 15 }))
                + '</div>';
            if (bp === 'desktop') {
                body += nbhField('Жирность вопроса', nbhSelect('design.entities.itemTitle.weight', [
                    { value: '400', label: '400' },
                    { value: '500', label: '500' },
                    { value: '600', label: '600' },
                    { value: '700', label: '700' },
                    { value: '800', label: '800' }
                ], '700'));
            }
            return body;
        }
        return body + '<div class="nbh-note">Этот preset уже зарезервирован для item/entity typography и будет расширен следующим этапом.</div>';
    },
    buttonStyle: function() {
        return '<div class="nbh-grid-2">'
            + nbhField('Primary style', nbhSelect('design.entities.primaryButton.style', [
                { value: 'primary', label: 'Primary' },
                { value: 'outline', label: 'Outline' },
                { value: 'ghost', label: 'Ghost' }
            ], 'primary'))
            + nbhField('Secondary style', nbhSelect('design.entities.secondaryButton.style', [
                { value: 'primary', label: 'Primary' },
                { value: 'outline', label: 'Outline' },
                { value: 'ghost', label: 'Ghost' }
            ], 'outline'))
            + '</div>';
    },
    surfaceStyle: function() {
        if (nbhBlockType() === 'faq') {
            return nbhField('Стиль карточек', nbhSelect('design.entities.itemSurface.variant', [
                { value: 'card', label: 'Card' },
                { value: 'plain', label: 'Plain' }
            ], 'card'));
        }
        return '<div class="nbh-note">Surface controls пойдут следующим слоем. Сейчас панель показывает, что сущность уже распознана и готова к общему стилевому контракту.</div>';
    },
    spacingLayout: function(panel, bp) {
        var body = nbhBreakpointToggle();
        if (bp === 'desktop') {
            return body + '<div class="nbh-grid-2">'
                + nbhField('Padding top', nbhInput('layout.desktop.paddingTop', { inputType: 'number', type: 'number', fallback: nbhBlockType() === 'faq' ? 88 : 96 }))
                + nbhField('Padding bottom', nbhInput('layout.desktop.paddingBottom', { inputType: 'number', type: 'number', fallback: nbhBlockType() === 'faq' ? 88 : 96 }))
                + (nbhBlockType() === 'faq' ? '' : nbhField('Min height', nbhInput('layout.desktop.minHeight', { inputType: 'number', type: 'number', fallback: 0 })))
                + nbhField('Content width', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: nbhBlockType() === 'faq' ? 760 : 640 }))
                + '</div>';
        }
        return body + '<div class="nbh-grid-2">'
            + nbhField('Padding top', nbhInput('layout.mobile.paddingTop', { inputType: 'number', type: 'number', fallback: 56 }))
            + nbhField('Padding bottom', nbhInput('layout.mobile.paddingBottom', { inputType: 'number', type: 'number', fallback: 56 }))
            + (nbhBlockType() === 'faq' ? '' : nbhField('Min height', nbhInput('layout.mobile.minHeight', { inputType: 'number', type: 'number', fallback: 0 })))
            + '</div>';
    },
    alignmentLayout: function() {
        if (nbhBlockType() === 'faq') {
            return nbhField('Выравнивание', nbhSelect('layout.desktop.align', [
                { value: 'center', label: 'Center' },
                { value: 'left', label: 'Left' }
            ], 'center'));
        }
        return nbhField('Режим hero', nbhSelect('layout.desktop.mode', [
            { value: 'centered', label: 'Centered' },
            { value: 'left', label: 'Left' },
            { value: 'split', label: 'Split' }
        ], 'centered'));
    },
    dataBindingSingle: function() {
        return '<div class="nbh-note">Источник данных и slot bindings уже предусмотрены state layer, но в hero prototype эта вкладка пока read-only. Следующий шаг — вывести source selector и compatible slot mapping.</div>';
    },
    dataBindingRepeater: function() {
        if (nbhBlockType() !== 'faq') {
            return '<div class="nbh-note">Repeater bindings подключены пока только для FAQ как первого content_list adapter.</div>';
        }

        var options = nbhDataOptions();
        var listSource = nbhFaqListSource();
        var fields = listSource.ctype && options.fieldsByType[listSource.ctype] ? options.fieldsByType[listSource.ctype] : [];
        var ctypeOptions = [{ value: '', label: 'Выберите тип контента' }].concat(options.contentTypes.map(function(ctype) {
            return { value: ctype.name, label: ctype.title };
        }));
        var fieldOptions = [{ value: '', label: 'Не выбрано' }].concat(fields.map(function(field) {
            return { value: field.name, label: field.label + ' [' + field.type + ']' };
        }));
        var body = nbhField('Источник списка', nbhSelect('data.listSource.type', options.listModes.length ? options.listModes : [
            { value: 'manual', label: 'Ручной список' },
            { value: 'content_list', label: 'Список записей InstantCMS' }
        ], 'manual'));

        if (listSource.type !== 'content_list') {
            return body + '<div class="nbh-note">Сейчас FAQ использует ручной список из вкладки Контент. Переключите источник на список записей InstantCMS, чтобы content.items[] собирался автоматически.</div>';
        }

        if (!options.contentTypes.length) {
            return body + '<div class="nbh-note">В системе не найдено включённых типов контента, поэтому content_list пока выбрать нельзя.</div>';
        }

        body += nbhField('Тип контента', nbhSelect('data.listSource.ctype', ctypeOptions, ''));
        body += '<div class="nbh-grid-2">'
            + nbhField('Лимит записей', nbhInput('data.listSource.limit', { inputType: 'number', type: 'number', fallback: 3 }))
            + nbhField('Сортировка', nbhSelect('data.listSource.sort', options.sortOptions.length ? options.sortOptions : [{ value: 'date_pub_desc', label: 'Сначала новые' }], 'date_pub_desc'))
            + '</div>';

        if (!listSource.ctype) {
            return body + '<div class="nbh-note">Сначала выберите тип контента, после этого появятся совместимые поля для вопроса и ответа.</div>';
        }

        if (!fields.length) {
            return body + '<div class="nbh-note">У выбранного типа контента не найдено текстовых полей для маппинга. Можно использовать системный title или выбрать другой ctype.</div>';
        }

        body += '<div class="nbh-grid-2">'
            + nbhField('Поле вопроса', nbhSelect('data.listSource.map.question', fieldOptions, 'title'))
            + nbhField('Поле ответа', nbhSelect('data.listSource.map.answer', fieldOptions, ''))
            + '</div>';
        body += nbhField('Если записей нет', nbhSelect('data.listSource.emptyBehavior', [
            { value: 'fallback', label: 'Показать ручной fallback' },
            { value: 'empty', label: 'Показать пустой список' }
        ], 'fallback'));
        body += '<div class="nbh-note">Ручные элементы из вкладки Контент остаются fallback-списком. Preview и live уже используют один и тот же SSR adapter pipeline.</div>';

        return body;
    },
    repeaterItems: function() {
        if (nbhBlockType() === 'faq') {
            return nbhRepeaterEditor();
        }
        return '<div class="nbh-note">Редактор repeater items будет подключён следующим этапом, после стабилизации hero contract runtime.</div>';
    },
    __default: function(panel) {
        return '<div class="nbh-note">Preset ' + panel.controlPreset + ' пока не подключен.</div>';
    }
};

function nbhRenderPanel(panel) {
    var bp = nbhState.activeBreakpoint;
    var renderer = nbhPresetRenderers[panel.controlPreset] || nbhPresetRenderers.__default;
    var body = renderer(panel, bp);

    return '<section class="nbh-section" data-panel="' + panel.key + '">' +
        '<div class="nbh-section-head"><strong>' + panel.label + '</strong><span>' + panel.section + '</span></div>' +
        '<div class="nbh-section-body">' + body + '</div>' +
    '</section>';
}

function nbhRenderPanels() {
    var panels = nbhPanelsForTab();
    var body = document.getElementById('nbh-panel-body');

    if (!panels.length) {
        body.innerHTML = '<div class="nbh-empty">Для текущего блока и выбранной сущности в этой вкладке нет активных панелей.</div>';
        return;
    }

    body.innerHTML = panels.map(nbhRenderPanel).join('');
}

function nbhRender() {
    if (!nbhState.loaded) return;
    document.getElementById('nbhEntityList').innerHTML = nbhEntityChipList();
    nbhRenderTabs();
    nbhSelectEntity(nbhState.selectedEntity, false);
}

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
    if (!target) return;
    nbhState.activeTab = target.dataset.tab;
    nbhRenderTabs();
    nbhRenderPanels();
});

document.getElementById('nbhEntityList').addEventListener('click', function(event) {
    var target = event.target.closest('[data-entity]');
    if (!target) return;
    nbhSelectEntity(target.dataset.entity, false);
});

document.getElementById('nbh-panel-body').addEventListener('input', function(event) {
    var target = event.target;
    if (target.closest('[data-breakpoint]')) return;
    if (target.dataset.itemField) {
        nbhUpdateRepeaterItem(parseInt(target.dataset.itemIndex || '0', 10), target.dataset.itemField, target.value);
        return;
    }
    var path = target.dataset.path;
    if (!path) return;

    var value = target.value;
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
    if (breakpointTarget) {
        nbhState.activeBreakpoint = breakpointTarget.dataset.breakpoint;
        nbhRenderPanels();
        return;
    }

    var target = event.target;
    if (target.dataset.itemField) {
        nbhUpdateRepeaterItem(parseInt(target.dataset.itemIndex || '0', 10), target.dataset.itemField, target.value);
        return;
    }
    var path = target.dataset.path;
    if (!path) return;

    var value = target.value;
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

document.getElementById('nbh-panel-body').addEventListener('click', function(event) {
    var repeaterAction = event.target.closest('[data-repeater-action]');
    if (repeaterAction) {
        if (repeaterAction.dataset.repeaterAction === 'add') {
            nbhAddRepeaterItem();
        }
        if (repeaterAction.dataset.repeaterAction === 'remove') {
            nbhRemoveRepeaterItem(parseInt(repeaterAction.dataset.itemIndex || '0', 10));
        }
        return;
    }

    var target = event.target.closest('[data-breakpoint]');
    if (!target) return;
    nbhState.activeBreakpoint = target.dataset.breakpoint;
    nbhRenderPanels();
});

document.getElementById('nbhVpDesktop').addEventListener('click', function() { nbhSetViewport('desktop'); });
document.getElementById('nbhVpMobile').addEventListener('click', function() { nbhSetViewport('mobile'); });

window.addEventListener('message', function(event) {
    var data = event.data || {};
    if (data.source !== 'nordicblocks-canvas') return;
    if (data.type === 'entity:selected' && data.entity) {
        nbhSelectEntity(data.entity, true);
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
</script>