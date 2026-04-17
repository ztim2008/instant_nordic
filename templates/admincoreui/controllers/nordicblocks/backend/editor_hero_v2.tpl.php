<?php
$block_title_esc = htmlspecialchars($block['title'], ENT_QUOTES, 'UTF-8');
$block_type      = htmlspecialchars($block['type'], ENT_QUOTES, 'UTF-8');
?>
<style>
#nbh-shell {
    position: fixed;
    inset: 55px 0 0 0;
    height: calc(100vh - 55px);
    max-height: calc(100vh - 55px);
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
    height: 100%;
    min-height: 0;
    flex: 1;
    overflow: hidden;
}
#nbh-canvas-wrap {
    padding: 16px;
    overflow: auto;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    min-height: 0;
    background: radial-gradient(circle at top left, rgba(96,165,250,.10), transparent 30%), #e2e8f0;
}
#nbh-canvas-frame {
    flex: 0 0 auto;
    width: 100%;
    max-width: 1280px;
    min-height: calc(100vh - 170px);
    border: none;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 18px 40px rgba(15,23,42,.16);
    transition: max-width .2s ease, height .18s ease;
}
#nbh-canvas-frame.is-mobile { max-width: 390px; }

#nbh-panel {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
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
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: .85rem;
    padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
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
    flex: 0 0 auto;
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
.nbh-accordion {
    display: flex;
    flex-direction: column;
    gap: .75rem;
}
.nbh-accordion-group {
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    overflow: hidden;
}
.nbh-accordion-group.is-open {
    border-color: #93c5fd;
    box-shadow: 0 0 0 1px rgba(147,197,253,.18);
}
.nbh-accordion-toggle {
    width: 100%;
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .85rem .95rem;
    cursor: pointer;
    text-align: left;
}
.nbh-accordion-toggle strong {
    display: block;
    font-size: .8rem;
    color: #0f172a;
}
.nbh-accordion-toggle span {
    display: block;
    margin-top: .12rem;
    font-size: .68rem;
    font-weight: 700;
    color: #64748b;
}
.nbh-accordion-icon {
    color: #64748b;
    transition: transform .16s ease, color .16s ease;
}
.nbh-accordion-group.is-open .nbh-accordion-icon {
    transform: rotate(180deg);
    color: #1d4ed8;
}
.nbh-accordion-body {
    display: none;
    padding: 0 .75rem .75rem;
    border-top: 1px solid #edf2f7;
    background: #f8fafc;
}
.nbh-accordion-group.is-open .nbh-accordion-body {
    display: flex;
    flex-direction: column;
    gap: .75rem;
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
.nbh-input-row {
    display: flex;
    gap: .45rem;
    align-items: stretch;
}
.nbh-input-row input {
    flex: 1 1 auto;
    min-width: 0;
}
.nbh-picker-btn {
    flex: 0 0 auto;
    border: 1px solid #d1d9e6;
    border-radius: 8px;
    background: #f8fafc;
    color: #334155;
    padding: 0 .75rem;
    font-size: .74rem;
    font-weight: 700;
    cursor: pointer;
    transition: border-color .15s, background .15s, color .15s;
}
.nbh-picker-btn:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #1d4ed8;
}
.nbh-picker-btn--clear:hover {
    border-color: #fca5a5;
    background: #fef2f2;
    color: #b91c1c;
}
.nbh-media-modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, .52);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    z-index: 3000;
}
.nbh-media-modal.is-open {
    display: flex;
}
.nbh-media-modal__dialog {
    width: min(920px, 100%);
    max-height: min(82vh, 760px);
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 30px 70px rgba(15, 23, 42, .28);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.nbh-media-modal__head,
.nbh-media-modal__toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .9rem 1rem;
}
.nbh-media-modal__head {
    border-bottom: 1px solid #e5edf5;
}
.nbh-media-modal__head strong {
    font-size: .95rem;
    color: #0f172a;
}
.nbh-media-modal__toolbar {
    border-bottom: 1px solid #eef2f7;
    flex-wrap: wrap;
}
.nbh-media-modal__pager,
.nbh-media-modal__actions {
    display: flex;
    align-items: center;
    gap: .55rem;
}
.nbh-media-modal__status {
    font-size: .76rem;
    font-weight: 700;
    color: #475569;
    min-width: 110px;
    text-align: center;
}
.nbh-media-modal__body {
    position: relative;
    min-height: 320px;
    padding: 1rem;
    overflow: auto;
    background: #f8fafc;
}
.nbh-media-modal__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: .85rem;
}
.nbh-media-card {
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    background: #fff;
    overflow: hidden;
    cursor: pointer;
    text-align: left;
    padding: 0;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
}
.nbh-media-card:hover {
    transform: translateY(-1px);
    border-color: #93c5fd;
    box-shadow: 0 10px 24px rgba(59, 130, 246, .12);
}
.nbh-media-card__thumb {
    display: block;
    aspect-ratio: 1 / 1;
    background: linear-gradient(135deg, #e2e8f0, #f8fafc);
    overflow: hidden;
}
.nbh-media-card__thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.nbh-media-card__name {
    display: block;
    padding: .7rem .75rem .8rem;
    font-size: .74rem;
    line-height: 1.4;
    color: #334155;
    word-break: break-word;
}
.nbh-media-modal__loading {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(248, 250, 252, .82);
    color: #334155;
    font-size: .86rem;
    font-weight: 700;
}
.nbh-media-modal__loading.is-hidden,
.nbh-media-modal__empty.is-hidden {
    display: none;
}
.nbh-media-modal__empty {
    padding: 2rem 1rem;
    text-align: center;
    color: #64748b;
    font-size: .82rem;
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
            <button type="button" class="is-active" id="nbhVpDesktop">Компьютер</button>
            <button type="button" id="nbhVpMobile">Мобильный</button>
        </div>
        <div class="nbh-sep"></div>
        <a class="nbh-btn nbh-btn--ghost" href="<?= htmlspecialchars($place_url, ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-thumb-tack"></i> Разместить</a>
        <button class="nbh-btn nbh-btn--save" id="nbhSaveBtn"><i class="fa fa-save"></i> Сохранить</button>
    </div>

    <div id="nbh-body">
        <div id="nbh-canvas-wrap">
            <iframe id="nbh-canvas-frame" src="<?= htmlspecialchars($canvas_url, ENT_QUOTES, 'UTF-8') ?>" title="Предпросмотр блока" sandbox="allow-same-origin allow-scripts"></iframe>
        </div>

        <div id="nbh-panel">
            <div class="nbh-panel-head">
                <div class="nbh-panel-title">
                    <strong>Инспектор v2</strong>
                    <span class="nbh-selection" id="nbhSelectionLabel">Сущность: заголовок</span>
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
var nbhIconPickerUrl = <?= json_encode(href_to('admin', 'settings', ['theme', cmsConfig::get('http_template'), 'icon_list']), JSON_UNESCAPED_UNICODE) ?>;
var nbhImagePickerListUrl = <?= json_encode(href_to('nordicblocks', 'media_list'), JSON_UNESCAPED_UNICODE) ?>;
var nbhImagePickerUploadUrl = <?= json_encode(href_to('nordicblocks', 'media_upload'), JSON_UNESCAPED_UNICODE) ?>;

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
    debounceTimer: null,
    openAccordionByTab: {}
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

function nbhHumanSection(sectionKey) {
    var labels = {
        text: 'Текст',
        actions: 'Действия',
        media: 'Медиа',
        repeaters: 'Повторы',
        section: 'Секция',
        typography: 'Типографика',
        surfaces: 'Поверхности',
        spacing: 'Отступы',
        alignment: 'Выравнивание',
        bindings: 'Данные'
    };
    return labels[sectionKey] || sectionKey;
}

function nbhBlockType() {
    return nbhGet(nbhState.server, 'block.type', '');
}

function nbhBlockUiProfile() {
    if (nbhHasEntity('items') && nbhHasCapability('repeaterContent')) {
        return {
            kind: 'collection',
            themeOptions: [
                { value: 'light', label: 'Светлая' },
                { value: 'alt', label: 'Серый фон' },
                { value: 'dark', label: 'Темная' }
            ],
            contentWidth: 760,
            title: {
                desktopFontSize: 48,
                mobileFontSize: 32,
                desktopMarginBottom: 0,
                mobileMarginBottom: 0,
                weight: '800',
                tag: 'h2',
                desktopExtras: true,
            },
            subtitle: {
                desktopFontSize: 18,
                mobileFontSize: 16,
                desktopMarginBottom: 32,
                mobileMarginBottom: 24,
                desktopExtras: true,
            },
            itemTypography: {
                enabled: true,
                questionDesktopSize: 18,
                questionMobileSize: 17,
                answerDesktopSize: 16,
                answerMobileSize: 15,
            },
            layout: {
                desktopPaddingTop: 88,
                desktopPaddingBottom: 88,
                mobilePaddingTop: 56,
                mobilePaddingBottom: 56,
                supportsMinHeight: false,
                primaryControl: 'align',
            },
        };
    }

    return {
        kind: 'hero',
        themeOptions: [
            { value: 'light', label: 'Светлая' },
            { value: 'dark', label: 'Темная' },
            { value: 'accent', label: 'Акцентная' }
        ],
        contentWidth: 640,
        title: {
            desktopFontSize: 64,
            mobileFontSize: 40,
            desktopMarginBottom: 16,
            mobileMarginBottom: 14,
            weight: '900',
            tag: 'h1',
            desktopExtras: false,
        },
        subtitle: {
            desktopFontSize: 20,
            mobileFontSize: 18,
            desktopMarginBottom: 24,
            mobileMarginBottom: 20,
            desktopExtras: false,
        },
        itemTypography: {
            enabled: false,
        },
        layout: {
            desktopPaddingTop: 96,
            desktopPaddingBottom: 96,
            mobilePaddingTop: 56,
            mobilePaddingBottom: 56,
            supportsMinHeight: true,
            primaryControl: 'mode',
        },
    };
}

function nbhRepeaterItems() {
    var items = nbhGet(nbhState.draft, 'content.items', []);
    return Array.isArray(items) ? items : [];
}

function nbhDataOptions() {
    var options = nbhState.server && nbhState.server.dataOptions ? nbhState.server.dataOptions : null;
    if (!options || typeof options !== 'object') {
        return { contentTypes: [], fieldsByType: {}, sourceModes: [], itemResolverModes: [], listModes: [], sortOptions: [] };
    }

    options.contentTypes = Array.isArray(options.contentTypes) ? options.contentTypes : [];
    options.fieldsByType = options.fieldsByType && typeof options.fieldsByType === 'object' ? options.fieldsByType : {};
    options.sourceModes = Array.isArray(options.sourceModes) ? options.sourceModes : [];
    options.itemResolverModes = Array.isArray(options.itemResolverModes) ? options.itemResolverModes : [];
    options.listModes = Array.isArray(options.listModes) ? options.listModes : [];
    options.sortOptions = Array.isArray(options.sortOptions) ? options.sortOptions : [];
    return options;
}

function nbhListSource() {
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
    if (typeof source.map.title !== 'string') source.map.title = typeof source.map.question === 'string' ? source.map.question : 'title';
    if (typeof source.map.text !== 'string') source.map.text = typeof source.map.answer === 'string' ? source.map.answer : '';
    source.map.question = source.map.title;
    source.map.answer = source.map.text;
    if (!source.emptyBehavior) source.emptyBehavior = 'fallback';

    nbhSet(nbhState.draft, 'data.listSource', source);
    return source;
}

function nbhBuildFaqItem(title, text) {
    title = typeof title === 'string' ? title : '';
    text = typeof text === 'string' ? text : '';

    return {
        title: title,
        text: text,
        question: title,
        answer: text
    };
}

function nbhFaqItemValue(item, key) {
    if (!item || typeof item !== 'object') {
        return '';
    }

    if (key === 'title') {
        return typeof item.title === 'string' ? item.title : (typeof item.question === 'string' ? item.question : '');
    }

    if (key === 'text') {
        return typeof item.text === 'string' ? item.text : (typeof item.answer === 'string' ? item.answer : '');
    }

    return typeof item[key] === 'string' ? item[key] : '';
}

function nbhSingleSource() {
    var source = nbhGet(nbhState.draft, 'data.source', null);
    if (!source || typeof source !== 'object' || Array.isArray(source)) {
        source = {};
    }

    if (!source.type) source.type = 'manual';
    if (!source.ctype) source.ctype = '';
    if (!source.resolver || typeof source.resolver !== 'object' || Array.isArray(source.resolver)) {
        source.resolver = {};
    }
    if (!source.resolver.mode) source.resolver.mode = 'current';

    var resolvedId = parseInt(source.resolver.id || source.resolver.itemId || source.resolver.item_id || 0, 10);
    source.resolver.id = isNaN(resolvedId) ? 0 : resolvedId;

    nbhSet(nbhState.draft, 'data.source', source);
    return source;
}

function nbhSingleBindings() {
    var defaults = {
        title: { mode: 'bound', formatter: 'plain_text', emptyBehavior: 'fallback' },
        subtitle: { mode: 'mixed', formatter: 'plain_text', emptyBehavior: 'fallback' },
        image: { mode: 'mixed', formatter: 'image_url', emptyBehavior: 'fallback' },
        imageAlt: { mode: 'mixed', formatter: 'plain_text', emptyBehavior: 'fallback' },
        date: { mode: 'bound', formatter: 'date_human', emptyBehavior: 'hide' },
        views: { mode: 'bound', formatter: 'number', emptyBehavior: 'hide' },
        comments: { mode: 'bound', formatter: 'number', emptyBehavior: 'hide' },
        primaryButtonUrl: { mode: 'mixed', formatter: 'record_url', emptyBehavior: 'fallback' }
    };
    var bindings = nbhGet(nbhState.draft, 'data.bindings', null);
    if (!bindings || typeof bindings !== 'object' || Array.isArray(bindings)) {
        bindings = {};
    }

    Object.keys(defaults).forEach(function(key) {
        var binding = bindings[key];
        if (!binding || typeof binding !== 'object' || Array.isArray(binding)) {
            binding = {};
        }
        if (!binding.mode) binding.mode = defaults[key].mode;
        if (typeof binding.field !== 'string') binding.field = '';
        if (!binding.formatter) binding.formatter = defaults[key].formatter;
        if (!binding.emptyBehavior) binding.emptyBehavior = defaults[key].emptyBehavior;
        bindings[key] = binding;
    });

    nbhSet(nbhState.draft, 'data.bindings', bindings);
    return bindings;
}

function nbhUsesCollectionData() {
    return nbhHasCapability('repeaterBindings') && nbhHasEntity('items');
}

function nbhFieldMatchesKinds(field, kinds) {
    var fieldKinds = Array.isArray(field && field.kinds) ? field.kinds : ['text'];
    var allowedKinds = Array.isArray(kinds) ? kinds : ['text'];
    return allowedKinds.some(function(kind) {
        return fieldKinds.indexOf(kind) !== -1;
    });
}

function nbhFieldOptionsByKinds(fields, kinds, emptyLabel) {
    fields = Array.isArray(fields) ? fields : [];
    return [{ value: '', label: emptyLabel || 'Не выбрано' }].concat(fields.filter(function(field) {
        return nbhFieldMatchesKinds(field, kinds);
    }).map(function(field) {
        return { value: field.name, label: field.label + ' [' + field.type + ']' };
    }));
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

function nbhNormalizeColor(value, fallback) {
    var normalized = String(value == null ? '' : value).trim();
    var safeFallback = String(fallback == null ? '#000000' : fallback).trim();

    if (/^#[0-9a-f]{6}$/i.test(normalized)) {
        return normalized;
    }

    if (/^#[0-9a-f]{3}$/i.test(normalized)) {
        return '#' + normalized.charAt(1) + normalized.charAt(1)
            + normalized.charAt(2) + normalized.charAt(2)
            + normalized.charAt(3) + normalized.charAt(3);
    }

    if (/^#[0-9a-f]{3}$/i.test(safeFallback)) {
        return '#' + safeFallback.charAt(1) + safeFallback.charAt(1)
            + safeFallback.charAt(2) + safeFallback.charAt(2)
            + safeFallback.charAt(3) + safeFallback.charAt(3);
    }

    if (/^#[0-9a-f]{6}$/i.test(safeFallback)) {
        return safeFallback;
    }

    return '#000000';
}

function nbhCommitPathValue(path, value, forceRerender) {
    nbhSet(nbhState.draft, path, value);
    if (forceRerender || nbhShouldRerenderPanels(path)) {
        nbhRenderPanels();
    }
    nbhMarkDirty();
    nbhScheduleSave();
}

var nbhImagePickerState = {
    path: '',
    page: 1,
    total: 0,
    perPage: 0
};

function nbhEnsureImagePickerModal() {
    if (document.getElementById('nbhImagePickerModal')) {
        return document.getElementById('nbhImagePickerModal');
    }

    var modal = document.createElement('div');
    modal.id = 'nbhImagePickerModal';
    modal.className = 'nbh-media-modal';
    modal.innerHTML = ''
        + '<div class="nbh-media-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="nbhImagePickerTitle">'
        + '<div class="nbh-media-modal__head">'
        + '<strong id="nbhImagePickerTitle">Выбрать изображение</strong>'
        + '<button type="button" class="nbh-picker-btn" data-media-close="1">Закрыть</button>'
        + '</div>'
        + '<div class="nbh-media-modal__toolbar">'
        + '<div class="nbh-media-modal__pager">'
        + '<button type="button" class="nbh-picker-btn" data-media-prev="1">Назад</button>'
        + '<span class="nbh-media-modal__status" data-media-status="1">Страница 1</span>'
        + '<button type="button" class="nbh-picker-btn" data-media-next="1">Вперед</button>'
        + '</div>'
        + '<div class="nbh-media-modal__actions">'
        + '<input type="file" accept="image/*" data-media-file="1" style="display:none;">'
        + '<button type="button" class="nbh-picker-btn" data-media-upload="1">Загрузить</button>'
        + '</div>'
        + '</div>'
        + '<div class="nbh-media-modal__body">'
        + '<div class="nbh-media-modal__loading is-hidden" data-media-loading="1"><span><i class="fa fa-spinner fa-spin"></i> Загружаем изображения...</span></div>'
        + '<div class="nbh-media-modal__empty is-hidden" data-media-empty="1">В библиотеке пока нет изображений.</div>'
        + '<div class="nbh-media-modal__grid" data-media-grid="1"></div>'
        + '</div>'
        + '</div>';

    document.body.appendChild(modal);

    modal.addEventListener('click', function(event) {
        var selectButton = event.target.closest('[data-media-select]');
        if (selectButton) {
            event.preventDefault();
            nbhCommitPathValue(nbhImagePickerState.path, selectButton.getAttribute('data-media-select') || '', true);
            nbhCloseImagePicker();
            return;
        }

        if (event.target === modal || event.target.closest('[data-media-close]')) {
            event.preventDefault();
            nbhCloseImagePicker();
            return;
        }

        if (event.target.closest('[data-media-prev]')) {
            event.preventDefault();
            if (nbhImagePickerState.page > 1) {
                nbhLoadImagePickerPage(nbhImagePickerState.page - 1);
            }
            return;
        }

        if (event.target.closest('[data-media-next]')) {
            event.preventDefault();
            var totalPages = Math.max(1, Math.ceil((nbhImagePickerState.total || 0) / (nbhImagePickerState.perPage || 1)));
            if (nbhImagePickerState.page < totalPages) {
                nbhLoadImagePickerPage(nbhImagePickerState.page + 1);
            }
            return;
        }

        if (event.target.closest('[data-media-upload]')) {
            event.preventDefault();
            modal.querySelector('[data-media-file]').click();
        }
    });

    modal.querySelector('[data-media-file]').addEventListener('change', function() {
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            return;
        }

        nbhSetImagePickerLoading(true, 'Загружаем изображение...');

        var formData = new FormData();
        formData.append('file', file);

        fetch(nbhImagePickerUploadUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('upload_failed');
            }
            return response.json();
        })
        .then(function(result) {
            var uploadedUrl = result && result.media && result.media.original ? result.media.original : (result && result.url ? result.url : '');
            if (!uploadedUrl) {
                throw new Error(result && result.error ? result.error : 'upload_failed');
            }
            nbhCommitPathValue(nbhImagePickerState.path, uploadedUrl, true);
            nbhCloseImagePicker();
        })
        .catch(function(error) {
            alert(error && error.message ? error.message : 'Не удалось загрузить изображение.');
        })
        .finally(function() {
            modal.querySelector('[data-media-file]').value = '';
            nbhSetImagePickerLoading(false);
        });
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            nbhCloseImagePicker();
        }
    });

    return modal;
}

function nbhSetImagePickerLoading(isLoading, message) {
    var modal = nbhEnsureImagePickerModal();
    var loading = modal.querySelector('[data-media-loading]');
    if (message) {
        loading.innerHTML = '<span><i class="fa fa-spinner fa-spin"></i> ' + nbhEscapeHtml(message) + '</span>';
    }
    loading.classList.toggle('is-hidden', !isLoading);
}

function nbhRenderImagePickerResult(result) {
    var modal = nbhEnsureImagePickerModal();
    var grid = modal.querySelector('[data-media-grid]');
    var empty = modal.querySelector('[data-media-empty]');
    var status = modal.querySelector('[data-media-status]');
    var prev = modal.querySelector('[data-media-prev]');
    var next = modal.querySelector('[data-media-next]');
    var images = result && Array.isArray(result.files) ? result.files : (result && Array.isArray(result.images) ? result.images : []);

    nbhImagePickerState.total = images.length;
    nbhImagePickerState.perPage = images.length || 1;

    status.textContent = images.length + ' изображений';
    prev.disabled = true;
    next.disabled = true;

    if (!images.length) {
        grid.innerHTML = '';
        empty.classList.remove('is-hidden');
        return;
    }

    empty.classList.add('is-hidden');
    grid.innerHTML = images.map(function(image) {
        var title = image && image.title ? image.title : 'Изображение';
        var url = image && image.media && image.media.original ? image.media.original : (image && image.url ? image.url : (image && image.preview_url ? image.preview_url : ''));
        var previewUrl = image && image.preview_url ? image.preview_url : url;
        return ''
            + '<button type="button" class="nbh-media-card" data-media-select="' + nbhEscapeAttr(url) + '">'
            + '<span class="nbh-media-card__thumb"><img src="' + nbhEscapeAttr(previewUrl) + '" alt="' + nbhEscapeAttr(title) + '"></span>'
            + '<span class="nbh-media-card__name">' + nbhEscapeHtml(title) + '</span>'
            + '</button>';
    }).join('');
}

function nbhLoadImagePickerPage(page) {
    nbhImagePickerState.page = Math.max(1, page || 1);
    nbhSetImagePickerLoading(true, 'Загружаем изображения...');

    fetch(nbhImagePickerListUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('list_failed');
        }
        return response.json();
    })
    .then(function(result) {
        nbhRenderImagePickerResult(result || {});
    })
    .catch(function() {
        var modal = nbhEnsureImagePickerModal();
        modal.querySelector('[data-media-grid]').innerHTML = '';
        modal.querySelector('[data-media-empty]').textContent = 'Не удалось загрузить системную библиотеку изображений.';
        modal.querySelector('[data-media-empty]').classList.remove('is-hidden');
    })
    .finally(function() {
        nbhSetImagePickerLoading(false);
    });
}

function nbhOpenImagePicker(path) {
    var modal = nbhEnsureImagePickerModal();
    nbhImagePickerState.path = path;
    nbhImagePickerState.page = 1;
    modal.classList.add('is-open');
    nbhLoadImagePickerPage(1);
}

function nbhCloseImagePicker() {
    nbhEnsureImagePickerModal().classList.remove('is-open');
}

function nbhOpenIconPicker(path) {
    if (!window.icms || !icms.modal || typeof icms.modal.openAjax !== 'function') {
        window.open(nbhIconPickerUrl, '_blank');
        return;
    }

    icms.modal.openAjax(nbhIconPickerUrl, {}, function() {
        Array.prototype.forEach.call(document.querySelectorAll('.icon-select'), function(icon) {
            icon.addEventListener('click', function(event) {
                event.preventDefault();
                nbhCommitPathValue(path, icon.getAttribute('data-name') || '', true);
                if (icms.modal && typeof icms.modal.close === 'function') {
                    icms.modal.close();
                }
                return false;
            }, { once: true });
        });
    }, 'Выбрать иконку');
}

function nbhAddRepeaterItem() {
    var items = nbhRepeaterItems().slice();
    items.push(nbhBuildFaqItem('Новый вопрос', 'Короткий ответ на вопрос.'));
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
        items[index] = nbhBuildFaqItem('', '');
    }

    var item = items[index];
    if (field === 'question') field = 'title';
    if (field === 'answer') field = 'text';

    item[field] = value;

    if (field === 'title') {
        item.question = value;
    }
    if (field === 'text') {
        item.answer = value;
    }

    if (typeof item.title !== 'string') item.title = typeof item.question === 'string' ? item.question : '';
    if (typeof item.text !== 'string') item.text = typeof item.answer === 'string' ? item.answer : '';
    item.question = item.title;
    item.answer = item.text;

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
    frame.style.height = '';
    frame.src = nbhCanvasUrl + (nbhCanvasUrl.indexOf('?') === -1 ? '?' : '&') + 't=' + Date.now();
}

function nbhApplyCanvasHeight(height) {
    var frame = document.getElementById('nbh-canvas-frame');
    var numericHeight = parseInt(height, 10);

    if (!frame || !numericHeight || numericHeight < 320) {
        return;
    }

    frame.style.height = numericHeight + 'px';
}

function nbhSyncCanvasHeightFromFrame() {
    var frame = document.getElementById('nbh-canvas-frame');
    var frameDoc;
    var body;
    var html;
    var height;

    if (!frame) return;

    try {
        frameDoc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
        body = frameDoc && frameDoc.body;
        html = frameDoc && frameDoc.documentElement;
        height = Math.max(
            body ? body.scrollHeight : 0,
            body ? body.offsetHeight : 0,
            html ? html.scrollHeight : 0,
            html ? html.offsetHeight : 0,
            html ? html.clientHeight : 0
        );
    } catch (error) {
        return;
    }

    nbhApplyCanvasHeight(height);
}

function nbhSelectEntity(entityKey, fromCanvas) {
    if (!entityKey) return;
    nbhState.selectedEntity = entityKey;
    var label = document.getElementById('nbhSelectionLabel');
    if (label) {
        label.textContent = 'Сущность: ' + nbhHumanEntity(entityKey);
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
    setTimeout(nbhSyncCanvasHeightFromFrame, 30);
}

function nbhBuildInspectorState(payload) {
    return {
        entityGroups: nbhGet(payload, 'inspector.entityGroups', {}),
        controls: nbhGet(payload, 'inspector.controls', nbhGet(payload, 'registry.controls', nbhGet(payload, 'inspector.controlPresets', {}))),
        controlPresets: nbhGet(payload, 'inspector.controlPresets', {}),
        availablePanels: nbhGet(payload, 'inspector.availablePanels', nbhGet(payload, 'registry.panels', [])),
        tabs: nbhGet(payload, 'inspector.tabs', nbhGet(payload, 'registry.tabs', [])),
        selectionModel: nbhGet(payload, 'inspector.selectionModel', {})
    };
}

function nbhPanelControlKey(panel) {
    if (!panel) {
        return '';
    }

    return panel.controlKey || panel.control || panel.controlPreset || '';
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
    var fallback = Object.prototype.hasOwnProperty.call(options, 'fallback') ? options.fallback : '';
    var value = nbhGet(nbhState.draft, path, fallback);
    var attrs = 'data-path="' + path + '"';
    var inputType = options.inputType || 'text';
    if (options.type) attrs += ' data-type="' + options.type + '"';
    if (inputType === 'color') {
        value = nbhNormalizeColor(value, fallback || '#000000');
    }

    var inputHtml = '<input ' + attrs + ' type="' + inputType + '" value="' + nbhEscapeAttr(value) + '">';

    if (options.picker === 'image' || options.picker === 'icon') {
        return '<div class="nbh-input-row">'
            + inputHtml
            + '<button type="button" class="nbh-picker-btn" data-picker-action="pick" data-picker-kind="' + options.picker + '" data-path="' + path + '">Выбрать</button>'
            + '<button type="button" class="nbh-picker-btn nbh-picker-btn--clear" data-picker-action="clear" data-path="' + path + '">Очистить</button>'
            + '</div>';
    }

    return inputHtml;
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
    return path.indexOf('data.listSource.') === 0
        || path.indexOf('data.source.') === 0
        || path.indexOf('data.bindings.') === 0
        || path.indexOf('design.section.background.') === 0;
}

function nbhYesNoOptions() {
    return [
        { value: '1', label: 'Показывать' },
        { value: '0', label: 'Скрыть' }
    ];
}

function nbhBreakpointToggle() {
    return '<div class="nbh-breakpoints"><button type="button" data-breakpoint="desktop" class="' + (nbhState.activeBreakpoint === 'desktop' ? 'is-active' : '') + '">Компьютер</button><button type="button" data-breakpoint="mobile" class="' + (nbhState.activeBreakpoint === 'mobile' ? 'is-active' : '') + '">Мобильный</button></div>';
}

function nbhRepeaterEditor() {
    var items = nbhRepeaterItems();
    var listSource = nbhListSource();
    var cards = items.map(function(item, index) {
        var question = nbhFaqItemValue(item, 'title');
        var answer = nbhFaqItemValue(item, 'text');
        return '<div class="nbh-note" style="background:#fff;border:1px solid #dbe4ef;">'
            + '<div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-bottom:.75rem;">'
            + '<strong>Вопрос ' + (index + 1) + '</strong>'
            + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="remove" data-item-index="' + index + '" style="padding:.32rem .7rem;font-size:.72rem;">Удалить</button>'
            + '</div>'
            + nbhField('Вопрос', '<input type="text" data-item-field="title" data-item-index="' + index + '" value="' + nbhEscapeAttr(question) + '">')
            + nbhField('Ответ', '<textarea data-item-field="text" data-item-index="' + index + '">' + nbhEscapeHtml(answer) + '</textarea>')
            + '</div>';
    }).join('');

    if (!cards) {
        cards = '<div class="nbh-note">Список FAQ пока пуст. Добавьте первый вопрос.</div>';
    }

    if (listSource.type === 'content_list') {
        cards = '<div class="nbh-note">Ручные вопросы ниже остаются резервным списком, если источник данных не вернёт записей.</div>' + cards;
    }

    return nbhField('Первый вопрос открыт', nbhSelect('runtime.disclosure.openFirst', [
        { value: '1', label: 'Да' },
        { value: '0', label: 'Нет' }
    ], '1'))
        + cards
        + '<button type="button" class="nbh-btn nbh-btn--ghost" data-repeater-action="add" style="align-self:flex-start;"><i class="fa fa-plus"></i> Добавить вопрос</button>';
}

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

var nbhControlComponentRenderers = {
    'text-content-panel': function(panel) {
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
        return '<div class="nbh-note">Для сущности ' + panel.entityScope + ' пока не подключена отдельная контентная панель.</div>';
    },
    'button-content-panel': function() {
        return '<div class="nbh-grid-2">'
            + nbhField('Текст основной кнопки', nbhInput('content.primaryButton.label'))
            + nbhField('Ссылка основной кнопки', nbhInput('content.primaryButton.url'))
            + nbhField('Текст вторичной кнопки', nbhInput('content.secondaryButton.label'))
            + nbhField('Ссылка вторичной кнопки', nbhInput('content.secondaryButton.url'))
            + '</div>';
    },
    'media-content-panel': function() {
        return nbhField('Путь к изображению', nbhInput('content.media.image', { picker: 'image' }))
            + nbhField('Alt-текст', nbhInput('content.media.alt'));
    },
    'section-background-panel': function() {
        var profile = nbhBlockUiProfile();
        var backgroundMode = String(nbhGet(nbhState.draft, 'design.section.background.mode', 'theme') || 'theme');
        var body = nbhField('Тема блока', nbhSelect('design.section.theme', profile.themeOptions, 'light'));
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
            body += nbhField('Путь к фото', nbhInput('design.section.background.image', { picker: 'image' }));
            body += '<div class="nbh-grid-2">'
                + nbhField('Цвет затемнения', nbhInput('design.section.background.overlayColor', { inputType: 'color', fallback: '#0f172a' }))
                + nbhField('Сила затемнения, %', nbhInput('design.section.background.overlayOpacity', { inputType: 'number', type: 'number', fallback: 45 }))
                + '</div>';
            body += '<div class="nbh-grid-2">'
                + nbhField('Позиция фото', nbhSelect('design.section.background.imagePosition', [
                    { value: 'center center', label: 'Центр' },
                    { value: 'top center', label: 'Сверху по центру' },
                    { value: 'bottom center', label: 'Снизу по центру' },
                    { value: 'center left', label: 'Слева по центру' },
                    { value: 'center right', label: 'Справа по центру' },
                    { value: 'top left', label: 'Левый верх' },
                    { value: 'top right', label: 'Правый верх' },
                    { value: 'bottom left', label: 'Левый низ' },
                    { value: 'bottom right', label: 'Правый низ' }
                ], 'center center'))
                + nbhField('Масштаб', nbhSelect('design.section.background.imageSize', [
                    { value: 'cover', label: 'Заполнить' },
                    { value: 'contain', label: 'Уместить целиком' },
                    { value: 'auto', label: 'Оригинал' }
                ], 'cover'))
                + '</div>';
            body += nbhField('Повтор', nbhSelect('design.section.background.imageRepeat', [
                { value: 'no-repeat', label: 'Без повтора' },
                { value: 'repeat', label: 'Повторять' },
                { value: 'repeat-x', label: 'Только по горизонтали' },
                { value: 'repeat-y', label: 'Только по вертикали' }
            ], 'no-repeat'));
        }

        return body;
    },
    'section-container-panel': function() {
        var profile = nbhBlockUiProfile();
        return nbhField('Ширина контента', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }));
    },
    'typography-text-panel': function(panel, bp) {
        var profile = nbhBlockUiProfile();
        var body = nbhBreakpointToggle();
        if (panel.entityScope === 'title') {
            body += '<div class="nbh-grid-2">'
                + nbhField('Размер', nbhInput('design.entities.title.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.title.desktopFontSize : profile.title.mobileFontSize }))
                + nbhField('Отступ снизу', nbhInput('design.entities.title.' + bp + '.marginBottom', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.title.desktopMarginBottom : profile.title.mobileMarginBottom }))
                + (bp === 'desktop'
                    ? nbhField('Жирность', nbhSelect('design.entities.title.weight', [
                        { value: '400', label: '400' },
                        { value: '500', label: '500' },
                        { value: '600', label: '600' },
                        { value: '700', label: '700' },
                        { value: '800', label: '800' },
                        { value: '900', label: '900' }
                    ], profile.title.weight))
                    : '')
                + '</div>';
            if (profile.title.desktopExtras && bp === 'desktop') {
                body += '<div class="nbh-grid-2">'
                    + nbhField('Цвет', nbhInput('design.entities.title.color', { inputType: 'color', fallback: '#0f172a' }))
                    + nbhField('Высота строки, %', nbhInput('design.entities.title.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 110 }))
                    + nbhField('Трекинг, px', nbhInput('design.entities.title.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                    + nbhField('Макс. ширина, px', nbhInput('design.entities.title.maxWidth', { inputType: 'number', type: 'number', fallback: 600 }))
                    + '</div>';
            }
            if (bp === 'desktop') {
                body += nbhField('HTML тег', nbhSelect('design.entities.title.tag', [
                    { value: 'div', label: 'DIV' },
                    { value: 'h1', label: 'H1' },
                    { value: 'h2', label: 'H2' },
                    { value: 'h3', label: 'H3' }
                ], profile.title.tag));
            }
            return body;
        }
        if (panel.entityScope === 'subtitle') {
            body += '<div class="nbh-grid-2">'
                + nbhField('Размер', nbhInput('design.entities.subtitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.subtitle.desktopFontSize : profile.subtitle.mobileFontSize }))
                + nbhField('Отступ снизу', nbhInput('design.entities.subtitle.' + bp + '.marginBottom', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.subtitle.desktopMarginBottom : profile.subtitle.mobileMarginBottom }))
                + '</div>';
            if (profile.subtitle.desktopExtras && bp === 'desktop') {
                body += '<div class="nbh-grid-2">'
                    + nbhField('Цвет', nbhInput('design.entities.subtitle.color', { inputType: 'color', fallback: '#475569' }))
                    + nbhField('Высота строки, %', nbhInput('design.entities.subtitle.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 165 }))
                    + nbhField('Трекинг, px', nbhInput('design.entities.subtitle.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                    + nbhField('Макс. ширина, px', nbhInput('design.entities.subtitle.maxWidth', { inputType: 'number', type: 'number', fallback: 720 }))
                    + '</div>';
            }
            return body;
        }
        if (panel.entityScope === 'items' && profile.itemTypography.enabled) {
            body += '<div class="nbh-grid-2">'
                + nbhField('Размер вопроса', nbhInput('design.entities.itemTitle.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.itemTypography.questionDesktopSize : profile.itemTypography.questionMobileSize }))
                + nbhField('Размер ответа', nbhInput('design.entities.itemText.' + bp + '.fontSize', { inputType: 'number', type: 'number', fallback: bp === 'desktop' ? profile.itemTypography.answerDesktopSize : profile.itemTypography.answerMobileSize }))
                + '</div>';
            if (bp === 'desktop') {
                body += nbhField('Жирность вопроса', nbhSelect('design.entities.itemTitle.weight', [
                    { value: '400', label: '400' },
                    { value: '500', label: '500' },
                    { value: '600', label: '600' },
                    { value: '700', label: '700' },
                    { value: '800', label: '800' }
                ], '700'));
                body += '<div class="nbh-grid-2">'
                    + nbhField('Цвет вопроса', nbhInput('design.entities.itemTitle.color', { inputType: 'color', fallback: '#0f172a' }))
                    + nbhField('Высота строки вопроса, %', nbhInput('design.entities.itemTitle.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 135 }))
                    + nbhField('Трекинг вопроса, px', nbhInput('design.entities.itemTitle.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                    + nbhField('Цвет ответа', nbhInput('design.entities.itemText.color', { inputType: 'color', fallback: '#475569' }))
                    + nbhField('Высота строки ответа, %', nbhInput('design.entities.itemText.lineHeightPercent', { inputType: 'number', type: 'number', fallback: 170 }))
                    + nbhField('Трекинг ответа, px', nbhInput('design.entities.itemText.letterSpacing', { inputType: 'number', type: 'number', fallback: 0 }))
                    + '</div>';
            }
            return body;
        }
        return body + '<div class="nbh-note">Этот набор настроек зарезервирован под типографику сущностей и будет расширен следующим этапом.</div>';
    },
    'button-style-panel': function() {
        return '<div class="nbh-grid-2">'
            + nbhField('Стиль основной кнопки', nbhSelect('design.entities.primaryButton.style', [
                { value: 'primary', label: 'Основная' },
                { value: 'outline', label: 'Контурная' },
                { value: 'ghost', label: 'Прозрачная' }
            ], 'primary'))
            + nbhField('Стиль вторичной кнопки', nbhSelect('design.entities.secondaryButton.style', [
                { value: 'primary', label: 'Основная' },
                { value: 'outline', label: 'Контурная' },
                { value: 'ghost', label: 'Прозрачная' }
            ], 'outline'))
            + '</div>';
    },
    'surface-style-panel': function() {
        if (nbhHasEntity('itemSurface') && nbhHasEntity('items')) {
            return nbhField('Стиль карточек', nbhSelect('design.entities.itemSurface.variant', [
                { value: 'card', label: 'Карточки' },
                { value: 'plain', label: 'Без карточек' }
            ], 'card'));
        }
        return '<div class="nbh-note">Настройки поверхности пойдут следующим слоем. Сейчас панель показывает, что сущность уже распознана и готова к общему стилевому контракту.</div>';
    },
    'spacing-layout-panel': function(panel, bp) {
        var profile = nbhBlockUiProfile();
        var body = nbhBreakpointToggle();
        if (bp === 'desktop') {
            return body + '<div class="nbh-grid-2">'
                + nbhField('Отступ сверху', nbhInput('layout.desktop.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingTop }))
                + nbhField('Отступ снизу', nbhInput('layout.desktop.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.desktopPaddingBottom }))
                + (profile.layout.supportsMinHeight ? nbhField('Мин. высота', nbhInput('layout.desktop.minHeight', { inputType: 'number', type: 'number', fallback: 0 })) : '')
                + nbhField('Ширина контента', nbhInput('layout.desktop.contentWidth', { inputType: 'number', type: 'number', fallback: profile.contentWidth }))
                + '</div>';
        }
        return body + '<div class="nbh-grid-2">'
            + nbhField('Отступ сверху', nbhInput('layout.mobile.paddingTop', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingTop }))
            + nbhField('Отступ снизу', nbhInput('layout.mobile.paddingBottom', { inputType: 'number', type: 'number', fallback: profile.layout.mobilePaddingBottom }))
            + (profile.layout.supportsMinHeight ? nbhField('Мин. высота', nbhInput('layout.mobile.minHeight', { inputType: 'number', type: 'number', fallback: 0 })) : '')
            + '</div>';
    },
    'alignment-layout-panel': function() {
        var profile = nbhBlockUiProfile();
        if (profile.layout.primaryControl === 'align') {
            return nbhField('Выравнивание', nbhSelect('layout.desktop.align', [
                { value: 'center', label: 'По центру' },
                { value: 'left', label: 'Слева' }
            ], 'center'));
        }
        return nbhField('Компоновка блока', nbhSelect('layout.desktop.mode', [
            { value: 'centered', label: 'По центру' },
            { value: 'left', label: 'Слева' },
            { value: 'split', label: 'Текст и медиа' }
        ], 'centered'));
    },
    'data-source-panel': function() {
        if (nbhUsesCollectionData()) {
            var listOptions = nbhDataOptions();
            var listSource = nbhListSource();
            var listCtypeOptions = [{ value: '', label: 'Выберите тип контента' }].concat(listOptions.contentTypes.map(function(ctype) {
                return { value: ctype.name, label: ctype.title };
            }));
            var listBody = nbhField('Источник данных', nbhSelect('data.listSource.type', listOptions.listModes.length ? listOptions.listModes : [
                { value: 'manual', label: 'Ручной список' },
                { value: 'content_list', label: 'Список записей InstantCMS' }
            ], 'manual'));

            if (listSource.type !== 'content_list') {
                return listBody + '<div class="nbh-note">Сейчас блок использует ручной список из вкладки Контент. Переключите источник на список записей InstantCMS, если коллекция должна собираться автоматически.</div>';
            }

            if (!listOptions.contentTypes.length) {
                return listBody + '<div class="nbh-note">В системе не найдено включённых типов контента, поэтому режим списка записей пока недоступен.</div>';
            }

            listBody += nbhField('Тип контента', nbhSelect('data.listSource.ctype', listCtypeOptions, ''));
            listBody += '<div class="nbh-grid-2">'
                + nbhField('Лимит записей', nbhInput('data.listSource.limit', { inputType: 'number', type: 'number', fallback: 3 }))
                + nbhField('Сортировка', nbhSelect('data.listSource.sort', listOptions.sortOptions.length ? listOptions.sortOptions : [{ value: 'date_pub_desc', label: 'Сначала новые' }], 'date_pub_desc'))
                + '</div>';

            if (!listSource.ctype) {
                return listBody + '<div class="nbh-note">Сначала выберите тип контента. После этого на соседней панели появятся совместимые поля для привязки элементов коллекции.</div>';
            }

            return listBody + '<div class="nbh-note">Ручные элементы из вкладки Контент остаются резервным слоем. Предпросмотр и публичный вывод продолжают использовать один и тот же SSR-конвейер данных.</div>';
        }

        var options = nbhDataOptions();
        var source = nbhSingleSource();
        nbhSingleBindings();

        var body = nbhField('Источник данных', nbhSelect('data.source.type', options.sourceModes.length ? options.sourceModes : [
            { value: 'manual', label: 'Ручной контент' },
            { value: 'content_item', label: 'Одна запись InstantCMS' }
        ], 'manual'));

        if (source.type !== 'content_item') {
            return body + '<div class="nbh-note">Сейчас блок использует ручной контент из вкладки Контент. Переключите источник на запись InstantCMS, если заголовок, подзаголовок, медиа и мета должны подтягиваться из системы.</div>';
        }

        if (!options.contentTypes.length) {
            return body + '<div class="nbh-note">В системе не найдено доступных типов контента, поэтому режим одной записи пока недоступен.</div>';
        }

        var ctypeOptions = [{ value: '', label: 'Выберите тип контента' }].concat(options.contentTypes.map(function(ctype) {
            return { value: ctype.name, label: ctype.title };
        }));

        body += nbhField('Тип контента', nbhSelect('data.source.ctype', ctypeOptions, ''));
        body += nbhField('Режим выборки', nbhSelect('data.source.resolver.mode', options.itemResolverModes.length ? options.itemResolverModes : [
            { value: 'current', label: 'Текущая запись страницы' },
            { value: 'by_id', label: 'Запись по ID' },
            { value: 'latest', label: 'Последняя запись' }
        ], 'current'));

        if (source.resolver.mode === 'by_id') {
            body += nbhField('ID записи', nbhInput('data.source.resolver.id', { inputType: 'number', type: 'number', fallback: 0 }));
        }

        if (source.resolver.mode === 'current') {
            body += '<div class="nbh-note">Режим текущей записи работает на реальной странице материала. В админском предпросмотре без контекста записи блок останется на ручных резервных значениях.</div>';
        }

        if (!source.ctype) {
            return body + '<div class="nbh-note">Сначала выберите тип контента, после этого появятся совместимые поля для привязки слотов.</div>';
        }

        var fields = options.fieldsByType[source.ctype] || [];
        if (!fields.length) {
            return body + '<div class="nbh-note">У выбранного типа контента не найдено доступных полей для привязки. Выберите другой тип контента или оставьте блок в ручном режиме.</div>';
        }

        var textOptions = nbhFieldOptionsByKinds(fields, ['text'], 'Оставить ручное значение');
        var imageOptions = nbhFieldOptionsByKinds(fields, ['image'], 'Оставить ручное изображение');
        var dateOptions = nbhFieldOptionsByKinds(fields, ['date', 'text'], 'Скрыть дату');
        var numberOptions = nbhFieldOptionsByKinds(fields, ['number', 'text'], 'Скрыть метрику');
        var urlOptions = nbhFieldOptionsByKinds(fields, ['url', 'text'], 'Оставить ручной URL');

        body += '<div class="nbh-grid-2">'
            + nbhField('Заголовок', nbhSelect('data.bindings.title.field', textOptions, ''))
            + nbhField('Подзаголовок', nbhSelect('data.bindings.subtitle.field', textOptions, ''))
            + nbhField('Изображение', nbhSelect('data.bindings.image.field', imageOptions, ''))
            + nbhField('Alt изображения', nbhSelect('data.bindings.imageAlt.field', textOptions, ''))
            + '</div>';

        body += '<div class="nbh-grid-2">'
            + nbhField('Дата', nbhSelect('data.bindings.date.field', dateOptions, ''))
            + nbhField('Просмотры', nbhSelect('data.bindings.views.field', numberOptions, ''))
            + nbhField('Комментарии', nbhSelect('data.bindings.comments.field', numberOptions, ''))
            + nbhField('Ссылка основной кнопки', nbhSelect('data.bindings.primaryButtonUrl.field', urlOptions, ''))
            + '</div>';

        body += '<div class="nbh-note">Ручные поля остаются резервным слоем. Если привязка не выбрана или запись не найдена, предпросмотр и публичный вывод продолжают работать на контенте из вкладки Контент.</div>';

        return body;
    },
    'data-collection-panel': function() {
        var options = nbhDataOptions();
        var listSource = nbhListSource();
        var fields = listSource.ctype && options.fieldsByType[listSource.ctype] ? options.fieldsByType[listSource.ctype] : [];

        if (listSource.type !== 'content_list') {
            return '<div class="nbh-note">Коллекция сейчас использует ручные элементы из вкладки Контент. Когда источник переключён на список записей, здесь появляются привязки полей элементов.</div>';
        }

        if (!listSource.ctype) {
            return '<div class="nbh-note">Сначала выберите тип контента на панели источника данных, после этого появятся совместимые поля для привязки элементов.</div>';
        }

        if (!fields.length) {
            return '<div class="nbh-note">У выбранного типа контента не найдено текстовых полей для привязки элементов. Можно использовать системный заголовок или выбрать другой тип контента.</div>';
        }

        var fieldOptions = [{ value: '', label: 'Не выбрано' }].concat(fields.map(function(field) {
            return { value: field.name, label: field.label + ' [' + field.type + ']' };
        }));
        var body = '<div class="nbh-grid-2">';

        if (nbhHasEntity('itemTitle')) {
            body += nbhField('Заголовок элемента', nbhSelect('data.listSource.map.title', fieldOptions, 'title'));
        }
        if (nbhHasEntity('itemText')) {
            body += nbhField('Текст элемента', nbhSelect('data.listSource.map.text', fieldOptions, ''));
        }

        body += '</div>';

        body += nbhField('Если записей нет', nbhSelect('data.listSource.emptyBehavior', [
            { value: 'fallback', label: 'Показать ручной резерв' },
            { value: 'empty', label: 'Показать пустой список' }
        ], 'fallback'));
        body += '<div class="nbh-note">Ручные элементы из вкладки Контент остаются резервным списком. Предпросмотр и публичный вывод уже используют один и тот же SSR-конвейер данных.</div>';

        return body;
    },
    'repeater-items-panel': function() {
        if (nbhHasCapability('repeaterContent') && nbhHasEntity('items')) {
            return nbhRepeaterEditor();
        }
        return '<div class="nbh-note">Редактор повторяющихся элементов будет подключён следующим этапом, после стабилизации hero runtime.</div>';
    },
    __default: function(panel, bp, control) {
        return '<div class="nbh-note">Панель ' + (control && control.label ? control.label : nbhPanelControlKey(panel)) + ' пока не подключена.</div>';
    }
};

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

    if (!panels.length) {
        body.innerHTML = '<div class="nbh-empty">Для текущего блока и выбранной сущности в этой вкладке нет активных панелей.</div>';
        return;
    }

    groups = nbhPanelSectionGroups(panels);
    activeKey = nbhActiveAccordionKey(groups);
    body.innerHTML = '<div class="nbh-accordion">' + groups.map(function(group) {
        return nbhRenderAccordionGroup(group, activeKey);
    }).join('') + '</div>';
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

    nbhCommitPathValue(path, value, false);
});

document.getElementById('nbh-panel-body').addEventListener('click', function(event) {
    var pickerAction = event.target.closest('[data-picker-action]');
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
});

window.addEventListener('message', function(event) {
    var data = event.data || {};
    if (data.source !== 'nordicblocks-canvas') return;
    if (data.type === 'canvas:metrics') {
        nbhApplyCanvasHeight(data.height);
        return;
    }
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