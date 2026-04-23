<?php
$page_title_esc = htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8');
$page_key_esc   = htmlspecialchars($page['key'],   ENT_QUOTES, 'UTF-8');

$this->setPageTitle('Редактор: ' . $page_title_esc);
$this->addBreadcrumb('NordicBlocks');
$this->addBreadcrumb('Страницы', $this->href_to('pages'));
$this->addBreadcrumb($page_title_esc);
$this->addMenuItems('admin_toolbar', $menu);

$this->addToolButton([
    'class' => 'save process-save',
    'title' => 'Сохранить',
    'href'  => '#',
    'icon'  => 'save'
]);

$this->addToolButton([
    'class' => 'preview',
    'title' => 'Просмотр',
    'href'  => $view_url,
    'icon'  => 'eye'
]);
?>

<style>
/* ── Редактор ── */
.nb-editor {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
    margin-top: 1rem;
}
/* Центральная колонка: стек блоков */
.nb-editor__canvas {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    min-height: 400px;
}
.nb-editor__canvas-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
}
.nb-block-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .75rem 1rem;
    margin-bottom: .75rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    cursor: default;
    transition: border-color .15s, box-shadow .15s;
}
.nb-block-item:hover      { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.15); }
.nb-block-item.is-selected{ border-color: #3b82f6; background: #eff6ff; }
.nb-block-item__handle { cursor: grab; color: #94a3b8; font-size: 1.1rem; }
.nb-block-item__title  { flex: 1; font-size: .9rem; font-weight: 500; }
.nb-block-item__type   { font-size: .75rem; color: #94a3b8; font-family: monospace; }
.nb-block-item__remove {
    background: none; border: none; cursor: pointer; color: #ef4444;
    padding: .25rem; font-size: .9rem; opacity: .5; transition: opacity .15s;
}
.nb-block-item__remove:hover { opacity: 1; }

/* ── Правая панель ── */
.nb-editor__panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    position: sticky;
    top: 80px;
}
.nb-panel-tabs {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
}
.nb-panel-tab {
    flex: 1;
    padding: .65rem .5rem;
    text-align: center;
    font-size: .82rem;
    font-weight: 500;
    cursor: pointer;
    color: #64748b;
    border: none;
    background: none;
    border-bottom: 2px solid transparent;
    transition: color .15s, border-color .15s;
}
.nb-panel-tab.is-active { color: #3b82f6; border-bottom-color: #3b82f6; }
.nb-panel-pane { display: none; padding: 1rem; }
.nb-panel-pane.is-active { display: block; }

/* Библиотека блоков */
.nb-block-lib-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem .75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: .5rem;
    cursor: pointer;
    transition: border-color .15s, background .15s;
}
.nb-block-lib-item:hover { border-color: #3b82f6; background: #eff6ff; }
.nb-block-lib-item__icon { font-size: 1.4rem; color: #64748b; width: 32px; text-align: center; }
.nb-block-lib-item__title { font-size: .85rem; font-weight: 500; }
.nb-block-lib-item__cat   { font-size: .72rem; color: #94a3b8; }

/* Инспектор */
.nb-inspector { font-size: .85rem; }
.nb-inspector-empty { text-align: center; color: #94a3b8; padding: 2rem 0; }
.nb-inspector-field   { margin-bottom: 1rem; }
.nb-inspector-field label {
    display: block;
    font-size: .78rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: .3rem;
}
.nb-inspector-field input,
.nb-inspector-field textarea,
.nb-inspector-field select {
    width: 100%;
    padding: .4rem .65rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: .85rem;
    box-sizing: border-box;
    outline: none;
    transition: border-color .15s;
}
.nb-inspector-field input:focus,
.nb-inspector-field textarea:focus,
.nb-inspector-field select:focus { border-color: #3b82f6; }
.nb-inspector-field textarea { resize: vertical; min-height: 72px; }

/* Preview strip (live tokens) */
.nb-editor__preview-bar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: .65rem 1rem;
    margin-bottom: 1rem;
    font-size: .8rem;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>

<!-- Инжектируем токены дизайн-системы -->
<style id="nb-tokens-inline"><?= $inline_css ?></style>

<div class="nb-editor" id="nbEditor">

    <!-- колонка: стек блоков -->
    <div>
        <div class="nb-editor__preview-bar">
            <span>
                <strong><?= $page_key_esc ?></strong>
                — токены дизайн-системы активны
            </span>
            <div style="display:flex;gap:.5rem;align-items:center;">
                <button type="button" id="nbSaveBtn" onclick="saveBlocks()" style="background:#3b82f6;color:#fff;border:none;padding:.35rem .9rem;border-radius:6px;font-size:.85rem;cursor:pointer;">
                    <i class="fa fa-save"></i> Сохранить
                </button>
                <a href="<?= htmlspecialchars($view_url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" style="color:#3b82f6;font-size:.85rem;">
                    <i class="fa fa-external-link-alt"></i> Открыть
                </a>
            </div>
        </div>

        <div class="nb-editor__canvas">
            <div id="nbBlockList">
                <!-- заполняется JS из nbState.blocks -->
            </div>
        </div>
    </div>

    <!-- правая панель -->
    <div class="nb-editor__panel">
        <div class="nb-panel-tabs">
            <button class="nb-panel-tab is-active" data-tab="library">
                <i class="fa fa-th-large"></i> Блоки
            </button>
            <button class="nb-panel-tab" data-tab="inspector">
                <i class="fa fa-sliders-h"></i> Инспектор
            </button>
        </div>

        <!-- Библиотека -->
        <div class="nb-panel-pane is-active" id="nbPaneLibrary">
            <?php foreach ($block_registry as $bname => $binfo):
                $btitle = htmlspecialchars($binfo['title'],    ENT_QUOTES, 'UTF-8');
                $bcat   = htmlspecialchars($binfo['category'], ENT_QUOTES, 'UTF-8');
                $btype  = htmlspecialchars($bname,             ENT_QUOTES, 'UTF-8');
            ?>
            <div class="nb-block-lib-item" data-add-block="<?= $btype ?>">
                <div class="nb-block-lib-item__icon"><i class="fa fa-puzzle-piece"></i></div>
                <div>
                    <div class="nb-block-lib-item__title"><?= $btitle ?></div>
                    <div class="nb-block-lib-item__cat"><?= $bcat ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Инспектор -->
        <div class="nb-panel-pane" id="nbPaneInspector">
            <div id="nbInspectorContent">
                <div class="nb-inspector-empty">
                    <i class="fa fa-mouse-pointer fa-2x mb-2"></i><br>
                    Выберите блок для редактирования
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Схемы блоков (JSON для инспектора) -->
<script>
var nbRegistry = <?= json_encode($block_registry, JSON_UNESCAPED_UNICODE) ?>;
var nbSaveUrl  = <?= json_encode($save_url, JSON_UNESCAPED_UNICODE) ?>;
var nbCsrfToken = <?= json_encode(cmsForm::getCSRFToken(), JSON_UNESCAPED_UNICODE) ?>;

var nbState = {
    blocks:   <?= json_encode($page['blocks'] ?? [], JSON_UNESCAPED_UNICODE) ?>,
    selected: null   // uid активного блока
};

/* ══ УТИЛИТЫ ══════════════════════════════════════════════════════ */

function genUid() {
    return 'b' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
}

function getSchema(type) {
    return nbRegistry[type] ? nbRegistry[type].schema : null;
}

function getBlock(uid) {
    return nbState.blocks.find(function(b) { return b.uid === uid; }) || null;
}

/* ══ РЕНДЕР СТЕКА БЛОКОВ ══════════════════════════════════════════ */

function renderBlockList() {
    var list = document.getElementById('nbBlockList');
    if (!nbState.blocks.length) {
        list.innerHTML = '<div class="nb-editor__canvas-empty">'
            + '<i class="fa fa-layer-group fa-2x mb-2"></i><br>'
            + 'Нет блоков. Добавьте первый из библиотеки справа.'
            + '</div>';
        return;
    }
    list.innerHTML = nbState.blocks.map(function(block, idx) {
        var schema   = getSchema(block.type);
        var title    = schema ? schema.title : block.type;
        var isActive = nbState.selected === block.uid;
        return '<div class="nb-block-item' + (isActive ? ' is-selected' : '') + '"'
            + ' data-uid="' + escAttr(block.uid) + '">'
            + '<span class="nb-block-item__handle" title="Перетащить"><i class="fa fa-grip-vertical"></i></span>'
            + (idx > 0
                ? '<button class="nb-block-item__remove" data-move-up="' + escAttr(block.uid) + '" title="Вверх"><i class="fa fa-chevron-up"></i></button>'
                : '<span style="width:28px"></span>'
              )
            + (idx < nbState.blocks.length - 1
                ? '<button class="nb-block-item__remove" style="color:#64748b" data-move-down="' + escAttr(block.uid) + '" title="Вниз"><i class="fa fa-chevron-down"></i></button>'
                : '<span style="width:28px"></span>'
              )
            + '<span class="nb-block-item__title" data-select="' + escAttr(block.uid) + '">' + esc(title) + '</span>'
            + '<span class="nb-block-item__type">' + esc(block.type) + '</span>'
            + '<button class="nb-block-item__remove" data-remove="' + escAttr(block.uid) + '" title="Удалить"><i class="fa fa-times"></i></button>'
            + '</div>';
    }).join('');

    bindBlockListEvents();
}

/* ══ ИНСПЕКТОР ════════════════════════════════════════════════════ */

function renderInspector(uid) {
    var block  = getBlock(uid);
    var schema = block ? getSchema(block.type) : null;
    var wrap   = document.getElementById('nbInspectorContent');

    if (!block || !schema) {
        wrap.innerHTML = '<div class="nb-inspector-empty">Выберите блок для редактирования</div>';
        return;
    }

    var fields = schema.fields || [];
    var html   = '<div class="nb-inspector">';
    html += '<p style="font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.75rem">'
          + esc(schema.title) + '</p>';

    fields.forEach(function(field) {
        var val = block.props[field.key] !== undefined
            ? block.props[field.key]
            : (field.default || '');
        html += '<div class="nb-inspector-field">';
        html += '<label for="nbi_' + escAttr(field.key) + '">' + esc(field.label) + '</label>';

        if (field.type === 'select') {
            html += '<select id="nbi_' + escAttr(field.key) + '" data-field="' + escAttr(field.key) + '">';
            (field.options || []).forEach(function(opt) {
                html += '<option value="' + escAttr(opt.value) + '"'
                     + (opt.value === val ? ' selected' : '')
                     + '>' + esc(opt.label) + '</option>';
            });
            html += '</select>';
        } else if (field.type === 'textarea') {
            html += '<textarea id="nbi_' + escAttr(field.key) + '" data-field="' + escAttr(field.key) + '">'
                 + esc(val) + '</textarea>';
        } else {
            var inputType = field.type === 'color' ? 'color' : 'text';
            html += '<input type="' + inputType + '" id="nbi_' + escAttr(field.key) + '"'
                 + ' data-field="' + escAttr(field.key) + '"'
                 + ' value="' + escAttr(String(val)) + '">';
        }
        html += '</div>';
    });

    html += '</div>';
    wrap.innerHTML = html;

    // Биндим изменения
    wrap.querySelectorAll('[data-field]').forEach(function(el) {
        el.addEventListener('input', function() {
            var key = el.dataset.field;
            block.props[key] = el.value;
            markDirty();
        });
    });
}

/* ══ СОБЫТИЯ СТЕКА ════════════════════════════════════════════════ */

function bindBlockListEvents() {
    document.querySelectorAll('[data-select]').forEach(function(el) {
        el.addEventListener('click', function() {
            var uid = el.dataset.select;
            nbState.selected = uid;
            renderBlockList();
            renderInspector(uid);
            switchTab('inspector');
        });
    });

    document.querySelectorAll('[data-remove]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
            var uid = el.dataset.remove;
            nbState.blocks = nbState.blocks.filter(function(b) { return b.uid !== uid; });
            if (nbState.selected === uid) {
                nbState.selected = null;
                renderInspector(null);
            }
            renderBlockList();
            markDirty();
        });
    });

    document.querySelectorAll('[data-move-up]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
            moveBlock(el.dataset.moveUp, -1);
        });
    });

    document.querySelectorAll('[data-move-down]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
            moveBlock(el.dataset.moveDown, 1);
        });
    });
}

function moveBlock(uid, direction) {
    var idx = nbState.blocks.findIndex(function(b) { return b.uid === uid; });
    var newIdx = idx + direction;
    if (newIdx < 0 || newIdx >= nbState.blocks.length) { return; }
    var tmp = nbState.blocks[idx];
    nbState.blocks[idx]    = nbState.blocks[newIdx];
    nbState.blocks[newIdx] = tmp;
    renderBlockList();
    markDirty();
}

/* ══ ДОБАВЛЕНИЕ БЛОКА ════════════════════════════════════════════ */

document.querySelectorAll('[data-add-block]').forEach(function(el) {
    el.addEventListener('click', function() {
        var type   = el.dataset.addBlock;
        var schema = getSchema(type);
        if (!schema) { return; }

        // Заполняем props дефолтными значениями из schema
        var props = {};
        (schema.fields || []).forEach(function(f) {
            props[f.key] = f.default || '';
        });

        var block = { type: type, uid: genUid(), props: props };
        nbState.blocks.push(block);
        nbState.selected = block.uid;
        renderBlockList();
        renderInspector(block.uid);
        switchTab('inspector');
        markDirty();
    });
});

/* ══ ТАБЫ ═════════════════════════════════════════════════════════ */

function switchTab(tabName) {
    document.querySelectorAll('.nb-panel-tab').forEach(function(t) {
        t.classList.toggle('is-active', t.dataset.tab === tabName);
    });
    document.querySelectorAll('.nb-panel-pane').forEach(function(p) {
        p.classList.toggle('is-active', p.id === 'nbPane' + capitalize(tabName));
    });
}

document.querySelectorAll('.nb-panel-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        switchTab(tab.dataset.tab);
    });
});

/* ══ СОХРАНЕНИЕ ══════════════════════════════════════════════════ */

var nbDirty   = false;
var nbSaving  = false;

function markDirty() {
    nbDirty = true;
    var btn = document.getElementById('nbSaveBtn');
    if (btn) { btn.style.outline = '2px solid #f59e0b'; }
}

function saveBlocks(callback) {
    if (nbSaving) { return; }
    nbSaving = true;
    fetch(nbSaveUrl + '?csrf_token=' + nbCsrfToken, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ blocks: nbState.blocks })
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        nbSaving = false;
        if (d.ok) {
            nbDirty = false;
            var btn = document.getElementById('nbSaveBtn');
            if (btn) { btn.style.background = '#16a34a'; btn.style.outline = ''; setTimeout(function(){ btn.style.background = '#3b82f6'; }, 1500); }
            if (callback) { callback(); }
        } else {
            alert('Ошибка сохранения: ' + (d.error || '?'));
        }
    })
    .catch(function() {
        nbSaving = false;
        alert('Ошибка соединения');
    });
}

// Предупреждение при уходе со страницы без сохранения
window.addEventListener('beforeunload', function(e) {
    if (nbDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
});

/* ══ ВСПОМОГАТЕЛЬНЫЕ ══════════════════════════════════════════════ */

function esc(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) { return esc(s); }
function capitalize(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

/* ══ ИНИЦИАЛИЗАЦИЯ ════════════════════════════════════════════════ */

renderBlockList();
if (nbState.selected) { renderInspector(nbState.selected); }
</script>
